document.addEventListener("DOMContentLoaded", () => {
  const tables = document.querySelectorAll(".admin-table, .custom-admin-table");

  const getRows = (table) =>
    table.dataset.recordCount === "0"
      ? []
      : Array.from(table.tBodies).flatMap((body) => Array.from(body.rows));

  const updateTable = (table, query = "", status = "all", page = 1) => {
    const rows = getRows(table);
    const normalizedQuery = query.trim().toLowerCase();
    const normalizedStatus = status.trim().toLowerCase();
    const matchingRows = rows.filter((row) => {
      const rowText = row.textContent.toLowerCase();
      const matchesQuery =
        !normalizedQuery || rowText.includes(normalizedQuery);
      const matchesStatus =
        normalizedStatus === "all" || rowText.includes(normalizedStatus);
      return matchesQuery && matchesStatus;
    });

    const container =
      table.closest(".table-responsive-card, section") || table.parentElement;
    const serverPagination = table.dataset.serverPagination === "true";
    const pagination = serverPagination
      ? null
      : container.querySelector(".pagination-controls, .pagination-wrapper");
    const pageSize = serverPagination ? matchingRows.length || 1 : 10;
    const pageCount = Math.max(1, Math.ceil(matchingRows.length / pageSize));
    const activePage = Math.min(page, pageCount);

    rows.forEach((row) => {
      row.hidden = true;
    });
    matchingRows
      .slice((activePage - 1) * pageSize, activePage * pageSize)
      .forEach((row) => {
        row.hidden = false;
      });

    let emptyState = table.parentElement.querySelector(
      ".dashboard-empty-state",
    );
    if (!emptyState) {
      emptyState = document.createElement("p");
      emptyState.className = "dashboard-empty-state";
      emptyState.textContent = "No matching records found.";
      table.parentElement.append(emptyState);
    }
    emptyState.hidden = matchingRows.length > 0;

    if (pagination) {
      const previousButton =
        pagination.querySelector('[data-page-action="previous"]') ||
        Array.from(pagination.querySelectorAll(".page-btn")).find(
          (button) => button.textContent.trim().toLowerCase() === "prev",
        );
      const nextButton =
        pagination.querySelector('[data-page-action="next"]') ||
        Array.from(pagination.querySelectorAll(".page-btn")).find(
          (button) => button.textContent.trim().toLowerCase() === "next",
        );

      if (previousButton) {
        previousButton.dataset.pageAction = "previous";
        previousButton.disabled = activePage === 1 || matchingRows.length === 0;
        previousButton.onclick = () =>
          updateTable(table, query, status, activePage - 1);
      }
      if (nextButton) {
        nextButton.dataset.pageAction = "next";
        nextButton.disabled =
          activePage === pageCount || matchingRows.length === 0;
        nextButton.onclick = () =>
          updateTable(table, query, status, activePage + 1);
      }

      pagination.querySelectorAll(".page-btn").forEach((button) => {
        const isPrevious = button === previousButton;
        const isNext = button === nextButton;
        if (!isPrevious && !isNext) button.remove();
      });
      if (matchingRows.length > 0) {
        const firstPage = Math.max(1, Math.min(activePage - 2, pageCount - 4));
        const lastPage = Math.min(pageCount, firstPage + 4);
        for (
          let pageNumber = firstPage;
          pageNumber <= lastPage;
          pageNumber += 1
        ) {
          const button = document.createElement("button");
          button.type = "button";
          button.className = "page-btn page-number";
          button.textContent = pageNumber;
          button.classList.toggle("active", pageNumber === activePage);
          button.onclick = () => updateTable(table, query, status, pageNumber);
          pagination.insertBefore(button, nextButton || null);
        }
      }

      pagination.hidden = matchingRows.length === 0 || pageCount === 1;
    }

    const footer = container.querySelector(
      ".table-pagination-footer, .table-footer",
    );
    const footerInfo = footer?.querySelector(".table-footer-info, span");
    if (footerInfo && !serverPagination) {
      const firstVisible =
        matchingRows.length === 0 ? 0 : (activePage - 1) * pageSize + 1;
      const lastVisible = Math.min(activePage * pageSize, matchingRows.length);
      footerInfo.textContent = `Showing ${firstVisible}-${lastVisible} of ${matchingRows.length}`;
    }
  };

  tables.forEach((table) => {
    if (table.dataset.dashboardTools === "false") return;
    const tableContainer =
      table.closest(".table-responsive-card, section") || table.parentElement;
    const localSearch = tableContainer.parentElement?.querySelector(
      ".toolbar-search input",
    );
    const filterToolbar =
      tableContainer.parentElement?.querySelector(".filter-pill-group");
    let query = "";
    let status = "all";

    if (localSearch) {
      localSearch.addEventListener("input", () => {
        query = localSearch.value;
        updateTable(table, query, status);
      });
    }

    filterToolbar?.querySelectorAll(".filter-pill").forEach((pill) => {
      pill.addEventListener("click", () => {
        filterToolbar
          .querySelectorAll(".filter-pill")
          .forEach((item) => item.classList.remove("active"));
        pill.classList.add("active");
        status = pill.textContent.trim();
        updateTable(table, query, status);
      });
    });

    updateTable(table);
  });

  document.querySelectorAll(".header-search input").forEach((searchInput) => {
    searchInput.addEventListener("input", () => {
      const query = searchInput.value.trim().toLowerCase();
      tables.forEach((table) => {
        getRows(table).forEach((row) => {
          row.hidden =
            Boolean(query) && !row.textContent.toLowerCase().includes(query);
        });
      });
    });
  });
});
