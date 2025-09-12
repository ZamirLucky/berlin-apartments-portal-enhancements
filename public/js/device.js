// ../../public/js/device.js
document.addEventListener("DOMContentLoaded", () => {
    const deviceTableBody  = document.getElementById("deviceTableBody");
    const deviceSearch     = document.getElementById("deviceSearch");
    const searchButton     = document.getElementById("searchButton");
    const sortButton       = document.getElementById("sortButton");
    const paginationControls = document.getElementById("paginationControls");

    // Original data or empty
    let originalData  = Array.isArray(deviceData) ? deviceData : [];
    let filteredData  = [...originalData];

    let currentPage   = 1;
    const itemsPerPage = 25;
    let sortDirection = 'asc';

    /**
     * Append a row for a single device
     */
    function appendDeviceRow(device) {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>${device.name || ""}</td>
            <td>${device.smartlockId || ""}</td>
            <td>${device.accountId || ""}</td>
        `;
        deviceTableBody.appendChild(row);
    }

    /**
     * Render table for current page
     */
    function renderTable(data, page) {
        deviceTableBody.innerHTML = "";
        const startIndex = (page - 1) * itemsPerPage;
        const endIndex   = Math.min(startIndex + itemsPerPage, data.length);
        const pageItems  = data.slice(startIndex, endIndex);

        if (pageItems.length > 0) {
            pageItems.forEach(device => appendDeviceRow(device));
        } else {
            deviceTableBody.innerHTML = `
                <tr>
                    <td colspan="3" class="text-center">No Data Found</td>
                </tr>`;
        }

        renderPaginationControls(data.length, page);
    }

    /**
     * Render pagination (Previous / Next buttons)
     */
    function renderPaginationControls(totalItems, page) {
        paginationControls.innerHTML = "";

        const totalPages = Math.ceil(totalItems / itemsPerPage);
        if (totalPages <= 1) return;

        // Previous
        const prevButton = document.createElement("button");
        prevButton.innerText = "Previous";
        prevButton.disabled   = page === 1;
        prevButton.classList.add("btn", "btn-primary", "me-2");
        prevButton.addEventListener("click", () => {
            if (currentPage > 1) {
                currentPage--;
                renderTable(filteredData, currentPage);
            }
        });

        // Next
        const nextButton = document.createElement("button");
        nextButton.innerText = "Next";
        nextButton.disabled   = page === totalPages;
        nextButton.classList.add("btn", "btn-primary");
        nextButton.addEventListener("click", () => {
            if (currentPage < totalPages) {
                currentPage++;
                renderTable(filteredData, currentPage);
            }
        });

        paginationControls.appendChild(prevButton);
        paginationControls.appendChild(nextButton);
    }

    /**
     * Filter table by search input
     */
    function filterTable() {
        const term = deviceSearch.value.trim().toLowerCase();
        if (!term) {
            filteredData = [...originalData];
        } else {
            filteredData = originalData.filter(d => {
                const nameLower = (d.name || "").toLowerCase();
                return nameLower.includes(term);
            });
        }
        sortData(filteredData);
        currentPage = 1;
        renderTable(filteredData, currentPage);
    }

    /**
     * Sort data by device name (asc/desc)
     */
    function sortData(data) {
        data.sort((a, b) => {
            const nameA = (a.name || "").toLowerCase();
            const nameB = (b.name || "").toLowerCase();
            if (nameA < nameB) return sortDirection === 'asc' ? -1 : 1;
            if (nameA > nameB) return sortDirection === 'asc' ? 1 : -1;
            return 0;
        });
    }

    // Sort button toggles direction
    sortButton.addEventListener("click", () => {
        sortDirection = (sortDirection === 'asc') ? 'desc' : 'asc';
        // Update icon
        const icon = sortButton.querySelector("i");
        if (sortDirection === 'asc') {
            icon.classList.remove("fa-sort-alpha-up-alt");
            icon.classList.add("fa-sort-alpha-down");
        } else {
            icon.classList.remove("fa-sort-alpha-down");
            icon.classList.add("fa-sort-alpha-up-alt");
        }
        sortData(filteredData);
        renderTable(filteredData, currentPage);
    });

    // Search button
    searchButton.addEventListener("click", filterTable);

    // Press Enter to trigger search
    deviceSearch.addEventListener("keypress", (e) => {
        if (e.key === "Enter") {
            e.preventDefault();
            filterTable();
        }
    });

    // Initial render
    sortData(filteredData);
    renderTable(filteredData, currentPage);
});
