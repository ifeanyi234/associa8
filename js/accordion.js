// Simple functionality for the FAQ Accordion
document.addEventListener("DOMContentLoaded", () => {
  const faqItems = document.querySelectorAll(".faq-item");

  faqItems.forEach((item) => {
    const toggleBtn = item.querySelector(".faq-toggle");

    toggleBtn.addEventListener("click", () => {
      // Check if the current item is already active
      const isActive = item.classList.contains("active");

      // Optional: Close all other accordions when one is opened
      faqItems.forEach((otherItem) => {
        otherItem.classList.remove("active");
      });

      // If it wasn't active, open it
      if (!isActive) {
        item.classList.add("active");
      }
    });
  });
});
