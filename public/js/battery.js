document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("searchInput");
    const searchButton = document.getElementById("searchButton");
    const tableBody = document.getElementById("smartlockTableBody");

    // Function to filter the table rows based on the search input
    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase(); // Get the input value and convert to lowercase for case-insensitive search
        const rows = tableBody.querySelectorAll("tr"); // Get all table rows

        // Loop through each row in the table
        rows.forEach((row) => {
            const smartlockName = row.querySelector("td").innerText.toLowerCase(); // Get the smartlock name from the first <td> in each row
            // Show or hide the row based on the search term
            if (smartlockName.includes(searchTerm)) {
                row.style.display = ""; // Show row if it matches the search
            } else {
                row.style.display = "none"; // Hide row if it doesn't match
            }
        });
    }

    // Listen for input in the search field (real-time search)
    searchInput.addEventListener("input", filterTable);

    // Listen for click on the search button
    searchButton.addEventListener("click", filterTable);
});

// Add a click event listener to the button with ID 'getSmartlockData'
document.getElementById("getSmartlockData").addEventListener("click", async function () {
    try {
        // Fetch the smartlock data from the PHP backend using GET request
        const response = await fetch("../../app/controllers/BatteryController.php", {
            method: "GET"
        });

        // Convert the response to JSON format
        const data = await response.json();

        // Get the 'result' div where data will be displayed
        let resultDiv = document.getElementById("result");
        resultDiv.innerHTML = ""; // Clear previous results

        // Handle cases where the server returns an error
        if (data.error) {
            resultDiv.innerHTML = `Error: ${data.error}`;
            return;
        }

        // If no devices are found, display a message
        if (data.length === 0) {
            resultDiv.innerHTML = "No devices found.";
        } else {
            // If data is returned, display each smartlock's information
            data.forEach(smartlock => {
                let smartlockInfo = `
                    <div class="smartlock-card mb-4 p-3 border rounded shadow-sm">
                        <h4>Smartlock: ${smartlock.name}</h4>
                        <p><strong>Battery Status:</strong> ${smartlock.state.batteryCritical ? "Critical" : "Normal"} (${smartlock.state.batteryCharge}%)</p>
                        <p><strong>Device State:</strong> ${smartlock.state.state}</p>
                    </div>
                `;
                resultDiv.innerHTML += smartlockInfo;
            });
        }
    } catch (error) {
        // Handle any errors that occur during fetch
        console.error("Error:", error);
        document.getElementById("result").innerHTML = "An error occurred while fetching the devices.";
    }
});
