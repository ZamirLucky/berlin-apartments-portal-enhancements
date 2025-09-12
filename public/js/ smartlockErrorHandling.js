document.addEventListener("DOMContentLoaded", function() {
    const smartlockData = window.smartlockData;

    if (smartlockData && smartlockData.error) {
        // Show the refresh button and handle the click event
        document.getElementById('refreshButton').addEventListener('click', function() {
            // Reload the page to attempt fetching the data again
            window.location.reload();
        });
    }
});
