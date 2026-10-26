(() => {
  const stylesheet = document.createElement("link");
  stylesheet.rel = "stylesheet";
  stylesheet.href = "/associa8/css/admin-notifications.css?v=20261024-1";
  document.head.append(stylesheet);

  const button = document.querySelector(".notification-btn");
  if (!button) return;

  const endpoint = "notification-feed.php";
  const panel = document.createElement("section");
  panel.className = "admin-notification-panel";
  panel.hidden = true;
  panel.setAttribute("aria-label", "Notifications");
  panel.innerHTML = '<header><strong>Notifications</strong><button type="button" class="admin-notification-read-all">Mark all read</button></header><div class="admin-notification-list" aria-live="polite"></div>';
  const anchor = document.createElement("div");
  anchor.className = "admin-notification-anchor";
  button.before(anchor);
  anchor.append(button, panel);
  button.type = "button";
  button.setAttribute("aria-haspopup", "true");
  button.setAttribute("aria-expanded", "false");

  let csrfToken = "";
  let isOpen = false;
  const badge = button.querySelector(".notification-badge");
  const list = panel.querySelector(".admin-notification-list");

  const updateBadge = (count) => {
    if (badge) {
      badge.textContent = count > 0 ? String(count > 99 ? "99+" : count) : "";
      badge.hidden = count < 1;
      badge.classList.toggle("has-count", count > 0);
    }
    button.setAttribute("aria-label", count > 0 ? `Notifications, ${count} unread` : "Notifications");
  };

  const loadNotifications = async () => {
    const response = await fetch(endpoint, { headers: { Accept: "application/json" }, credentials: "same-origin" });
    if (!response.ok) throw new Error("Notifications could not be loaded.");
    const data = await response.json();
    csrfToken = data.csrf_token;
    updateBadge(Number(data.unread_count) || 0);
    list.replaceChildren();
    if (!Array.isArray(data.notifications) || data.notifications.length === 0) {
      const empty = document.createElement("p");
      empty.className = "admin-notification-empty";
      empty.textContent = "You are all caught up.";
      list.append(empty);
      return;
    }
    data.notifications.forEach((item) => {
      const row = document.createElement("a");
      row.className = `admin-notification-item${item.is_read ? "" : " is-unread"}`;
      row.href = item.link || "dashboard.php";
      const title = document.createElement("strong");
      title.textContent = item.title;
      const message = document.createElement("span");
      message.textContent = item.message;
      const date = document.createElement("time");
      date.textContent = item.created_at;
      row.append(title, message, date);
      row.addEventListener("click", async (event) => {
        if (!item.is_read) {
          event.preventDefault();
          try {
            await markRead("read", item.id);
            window.location.assign(row.href);
          } catch (error) {
            list.textContent = error.message;
          }
        }
      });
      list.append(row);
    });
  };

  const markRead = async (action, notificationId = null) => {
    const body = new FormData();
    body.set("csrf_token", csrfToken);
    body.set("action", action);
    if (notificationId !== null) body.set("notification_id", String(notificationId));
    const response = await fetch(endpoint, { method: "POST", body, credentials: "same-origin" });
    if (!response.ok) throw new Error("Notification state could not be saved.");
    await loadNotifications();
  };

  button.addEventListener("click", async () => {
    isOpen = !isOpen;
    panel.hidden = !isOpen;
    button.setAttribute("aria-expanded", String(isOpen));
    if (isOpen) {
      try {
        await loadNotifications();
      } catch (error) {
        list.textContent = error.message;
      }
    }
  });
  panel.querySelector(".admin-notification-read-all").addEventListener("click", async () => {
    try {
      await markRead("read_all");
    } catch (error) {
      list.textContent = error.message;
    }
  });
  document.addEventListener("click", (event) => {
    if (!panel.contains(event.target) && !button.contains(event.target)) {
      isOpen = false;
      panel.hidden = true;
      button.setAttribute("aria-expanded", "false");
    }
  });

  loadNotifications().catch((error) => console.error(error));
  window.setInterval(() => {
    if (!document.hidden) loadNotifications().catch((error) => console.error(error));
  }, 60000);
})();
