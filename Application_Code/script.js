// Wait until the DOM is fully loaded
document.addEventListener("DOMContentLoaded", function() {
  
    // Example: Form validation for the search form on search.php
    const searchForm = document.querySelector("form[action='search.php']");
    
    if (searchForm) {
      searchForm.addEventListener("submit", function(e) {
        const startDate = document.getElementById("start_date");
        const endDate = document.getElementById("end_date");
        
        // If one date is filled, the other must also be filled.
        if ((startDate && startDate.value && !endDate.value) ||
            (endDate && endDate.value && !startDate.value)) {
          e.preventDefault();
          alert("Please enter both a start and an end date.");
        }
        
        // You could add more validations here as needed.
      });
    }
  
    // Additional interactivity can be added here
    // For example: dynamically updating search results without reloading,
    // toggling visibility of form sections, etc.
  });
  