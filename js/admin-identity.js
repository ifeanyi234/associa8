const applyAdminIdentity = () => {
  const identity = window.Associa8AdminIdentity;
  if (!identity) return;

  const organizationName = identity.organizationName || identity.accountName || "Associa8";
  const role = identity.role || "";
  const roleLabels = {
    super_admin: "Current access: Super Admin",
    admin: "Current access: Admin",
    zonal_admin: "Current access: Zonal Admin",
    subzonal_admin: "Current access: Sub-zonal Admin",
  };
  const roleDetails = {
    super_admin: "Organization owner access across organization modules.",
    admin: "Administrator access across organization modules.",
    zonal_admin: "Dashboard and member actions for your assigned zone and its sub-zones.",
    subzonal_admin: "Dashboard and member actions for your assigned sub-zone.",
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
  document.querySelectorAll(".avatar-badge:not(.member-avatar)").forEach((element) => {
    element.textContent = initials.toUpperCase();
    element.setAttribute("aria-label", organizationName);
  });
};

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", applyAdminIdentity, { once: true });
} else {
  applyAdminIdentity();
}
