// ../../public/js/user.js

document.addEventListener("DOMContentLoaded", () => {
    const tableBody = document.getElementById("userTableBody");
    const userSearchInput = document.getElementById("userSearch");
    const searchButton = document.getElementById("searchButton");
    const sortButton = document.getElementById("sortButton");
    const resetButton = document.getElementById("resetButton");
    const paginationControls = document.getElementById("paginationControls");

    let originalUserData = Array.isArray(userData) ? userData : [];
    let filteredData = [...originalUserData];

    let currentPage = 1;
    const usersPerPage = 25;
    let sortDirection = 'asc';

    /**
     * Creates a table row for a user
     */
    function appendUserRow(user) {
        const row = document.createElement("tr");

        // Format creation date
        let formattedCreationDate = "N/A";
        if (user.creationDate) {
            const dateObj = new Date(user.creationDate);
            if (!isNaN(dateObj.getTime())) {
                const month = String(dateObj.getMonth() + 1).padStart(2, '0');
                const day = String(dateObj.getDate()).padStart(2, '0');
                const year = dateObj.getFullYear();
                formattedCreationDate = `${month}/${day}/${year}`;
            }
        }

        row.innerHTML = `
            <td>${user.userId || ""}</td>
            <td>${user.accountId || ""}</td>
            <td>${user.email || ""}</td>
            <td>${user.name || user.userName || ""}</td>
            <td>${formattedCreationDate}</td>
        `;
        tableBody.appendChild(row);
    }

    /**
     * Renders the table for a specific page
     */
    function renderTable(data, page) {
        tableBody.innerHTML = '';

        const startIndex = (page - 1) * usersPerPage;
        const endIndex = Math.min(startIndex + usersPerPage, data.length);
        const usersToDisplay = data.slice(startIndex, endIndex);

        if (usersToDisplay.length > 0) {
            usersToDisplay.forEach(user => appendUserRow(user));
        } else {
            tableBody.innerHTML = 
                '<tr><td colspan="5" class="text-center">No Data Found</td></tr>';
        }

        renderPaginationControls(data.length, page);
    }

    /**
     * Renders pagination controls (Previous / Next + current page info)
     */
    function renderPaginationControls(totalUsers, page) {
        paginationControls.innerHTML = "";

        const totalPages = Math.ceil(totalUsers / usersPerPage);
        if (totalPages <= 1) return;

        // Show page info
        const pageInfo = document.createElement("span");
        pageInfo.classList.add("me-3");
        pageInfo.textContent = `Page ${page} of ${totalPages}`;

        paginationControls.appendChild(pageInfo);

        // Prev Button
        const prevButton = document.createElement("button");
        prevButton.innerText = "Previous";
        prevButton.disabled = page === 1;
        prevButton.classList.add("btn", "btn-primary", "me-2");
        prevButton.addEventListener("click", () => {
            if (currentPage > 1) {
                currentPage--;
                renderTable(filteredData, currentPage);
            }
        });

        // Next Button
        const nextButton = document.createElement("button");
        nextButton.innerText = "Next";
        nextButton.disabled = page === totalPages;
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
     * Filters the table based on search input
     */
    function filterTable() {
        const term = userSearchInput.value.trim().toLowerCase();
        if (!term) {
            // if search is empty, just restore the original data
            filteredData = [...originalUserData];
        } else {
            filteredData = originalUserData.filter(user => {
                const combinedName = (user.name || user.userName || "").toLowerCase();
                const email = (user.email || "").toLowerCase();
                return combinedName.includes(term) || email.includes(term);
            });
        }
        sortData(filteredData);
        currentPage = 1;
        renderTable(filteredData, currentPage);
    }

    /**
     * Sorts data by name (or userName) in ascending/descending
     */
    function sortData(data) {
        data.sort((a, b) => {
            const nameA = (a.name || a.userName || "").toLowerCase();
            const nameB = (b.name || b.userName || "").toLowerCase();
            if (nameA < nameB) return sortDirection === 'asc' ? -1 : 1;
            if (nameA > nameB) return sortDirection === 'asc' ? 1 : -1;
            return 0;
        });
    }

    /**
     * Handle sort button click
     */
    sortButton.addEventListener('click', () => {
        // Toggle direction
        sortDirection = (sortDirection === 'asc') ? 'desc' : 'asc';

        // Update icon
        const sortIcon = sortButton.querySelector('i');
        if (sortDirection === 'asc') {
            sortIcon.classList.remove('fa-sort-alpha-up-alt');
            sortIcon.classList.add('fa-sort-alpha-down');
        } else {
            sortIcon.classList.remove('fa-sort-alpha-down');
            sortIcon.classList.add('fa-sort-alpha-up-alt');
        }

        sortData(filteredData);
        renderTable(filteredData, currentPage);
    });

    /**
     * Handle search
     */
    searchButton.addEventListener('click', filterTable);
    userSearchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            filterTable();
        }
    });

    /**
     * Handle reset button
     */
    resetButton.addEventListener('click', () => {
        userSearchInput.value = "";
        filteredData = [...originalUserData];
        sortData(filteredData);
        currentPage = 1;
        renderTable(filteredData, currentPage);
    });

    // Initial load
    if (originalUserData.length > 0) {
        sortData(filteredData);
        renderTable(filteredData, currentPage);
    } else {
        console.warn("No valid user data found:", originalUserData);
        renderTable([], 1);
    }
});
