const applyAdminIdentity = () => {
  const identity = window.Associa8AdminIdentity;
  if (!identity) return;

  const organizationName = identity.organizationName || identity.accountName || "Associa8";
  const role = identity.role || "";
  const roleLabels = {
    super_admin: "Current access: Super Admin",
    admin: "Current access: Admin",
    manager: "Current access: Manager",
    staff: "Current access: Staff",
  };
  const roleDetails = {
    super_admin: "Organization owner access across organization modules.",
    admin: "Administrator access across organization modules.",
    manager: "Operational access to members, admissions, documents, events, and attendance.",
    staff: "Limited access to members, documents, events, and attendance.",
  };
  const nameParts = organizationName.trim().split(/\s+/).filter(Boolean);
  const initials = nameParts.length > 1
    ? `${nameParts[0][0]}${nameParts[1][0]}`
    : organizationName.slice(0, 2);

  document.querySelectorAll(".user-name").forEach((element) => {
    element.textContent = organizationName;
    element.setAttribute("title", organizationName);
  });
  document.querySelectorAll(".user-role").forEach((element) => {
    element.textContent = roleLabels[role] || "Current access";
    element.setAttribute("title", roleDetails[role] || "Your organization's assigned access level.");
  });
  document.querySelectorAll(".avatar-badge").forEach((element) => {
    element.textContent = initials.toUpperCase();
    element.setAttribute("aria-label", organizationName);
  });
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", applyAdminIdentity, { once: true });
} else {
  applyAdminIdentity();
}
