// public/js/Smartlock.js

document.addEventListener("DOMContentLoaded", () => {
    console.log("[Smartlock.js] Script loaded.");

    const tableBody          = document.getElementById("smartlockTableBody");
    const searchButton       = document.getElementById("searchButton");
    const searchInput        = document.getElementById("searchInput");
    const paginationControls = document.getElementById("paginationControls");

    // Ensure `smartlockData` is an array
    let data = Array.isArray(smartlockData) ? smartlockData : [];

    let currentPage    = 1;
    const itemsPerPage = 10;

    function renderTable(dataset, page) {
        tableBody.innerHTML = "";

        // If there's no data, clear pagination and return
        if (!Array.isArray(dataset) || dataset.length === 0) {
            paginationControls.innerHTML = "";
            return;
        }

        const totalItems = dataset.length;
        const totalPages = Math.ceil(totalItems / itemsPerPage);

        if (page < 1) page = 1;
        if (page > totalPages) page = totalPages;

        const startIndex = (page - 1) * itemsPerPage;
        const endIndex   = Math.min(startIndex + itemsPerPage, totalItems);

        for (let i = startIndex; i < endIndex; i++) {
            const item = dataset[i];
            if (!item) {
                console.warn("No item found at index:", i);
                continue;
            }

            // If accountUserId is null, disable the "Send Code" button
            const disableSendButton = (item.accountUserId === null);

            // Friendly tooltip text for disabled case
            const tooltipMessage = "Unable to send code due to missing user ID";

            // Conditionally wrap the button if it's disabled
            // so we can show a hover message
            const sendCodeHTML = disableSendButton
                ? `
                  <div class="tooltip-wrapper"
                       data-tooltip-message="${tooltipMessage}">
                    <button class="btn btn-primary send-code-btn"
                            data-id="${item.smartlockId}"
                            data-account-user-id="${item.accountUserId}"
                            disabled>
                        Send Code
                    </button>
                  </div>
                  `
                : `
                  <button class="btn btn-primary send-code-btn"
                          data-id="${item.smartlockId}"
                          data-account-user-id="${item.accountUserId}">
                      Send Code
                  </button>
                  `;

            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${item.deviceName       || "N/A"}</td>
                <td>${item.userName         || "N/A"}</td>
                <td class="text-center">${item.creationDate     || "N/A"}</td>
                <td class="text-center">${item.allowedFromDate  || "N/A"}</td>
                <td class="text-center">${item.allowedUntilDate || "N/A"}</td>
                <td class="text-center">
                    ${sendCodeHTML}
                    <button class="btn btn-warning extend-date-btn"
                            data-id="${item.id}">
                        Extend Date
                    </button>
                </td>
            `;
            tableBody.appendChild(tr);
        }

        renderPaginationControls(totalPages, page, dataset);

        // IMPORTANT: After rendering, attach tooltip events
        initTooltips();
    }

    function renderPaginationControls(totalPages, current, dataset) {
        paginationControls.innerHTML = "";
        if (totalPages <= 1) return;

        const prevBtn = document.createElement("button");
        prevBtn.textContent = "Previous";
        prevBtn.classList.add("btn", "btn-secondary", "me-2");
        prevBtn.disabled = (current === 1);
        prevBtn.addEventListener("click", () => {
            currentPage--;
            renderTable(dataset, currentPage);
        });
        paginationControls.appendChild(prevBtn);

        const nextBtn = document.createElement("button");
        nextBtn.textContent = "Next";
        nextBtn.classList.add("btn", "btn-secondary");
        nextBtn.disabled = (current === totalPages);
        nextBtn.addEventListener("click", () => {
            currentPage++;
            renderTable(dataset, currentPage);
        });
        paginationControls.appendChild(nextBtn);
    }

    // Initial table rendering
    renderTable(data, currentPage);

    // Search functionality
    if (searchButton && searchInput) {
        searchButton.addEventListener("click", () => {
            const term = (searchInput.value || "").trim().toLowerCase();
            if (!term) {
                currentPage = 1;
                renderTable(data, currentPage);
                return;
            }
            const filtered = data.filter(item => {
                const dev = (item.deviceName || "").toLowerCase();
                const usr = (item.userName   || "").toLowerCase();
                return dev.includes(term) || usr.includes(term);
            });
            currentPage = 1;
            renderTable(filtered, currentPage);
        });

        searchInput.addEventListener("keyup", (e) => {
            if (e.key === "Enter") {
                searchButton.click();
            }
        });
    }

    // Delegate clicks for buttons
    document.addEventListener("click", (event) => {
        if (event.target.classList.contains("send-code-btn")) {
            handleSendCodeButton(event.target);
        } else if (event.target.classList.contains("extend-date-btn")) {
            handleExtendDateButton(event.target);
        }
    });

    /*
     * ===========================================================
     *  PURE JAVASCRIPT TOOLTIP LOGIC (no Bootstrap tooltips)
     * ===========================================================
     *  -> Updated to use direct event listeners on each tooltip-wrapper
     */
    let tooltipDiv = null;

    function initTooltips() {
        // 1) Remove any old listeners (in a real scenario, you might track them and remove if needed)
        // 2) Add "mouseenter" and "mouseleave" to each .tooltip-wrapper
        const wrappers = document.querySelectorAll(".tooltip-wrapper");
        wrappers.forEach((wrapper) => {
            wrapper.addEventListener("mouseenter", createTooltip);
            wrapper.addEventListener("mouseleave", removeTooltip);
        });
    }

    function createTooltip(event) {
        const wrapper = event.currentTarget; // The .tooltip-wrapper
        const tooltipMessage = wrapper.getAttribute("data-tooltip-message");
        if (!tooltipMessage) return;

        // Create the tooltip element
        tooltipDiv = document.createElement("div");
        tooltipDiv.className = "js-tooltip";
        tooltipDiv.textContent = tooltipMessage;

        // Append it to the body
        document.body.appendChild(tooltipDiv);

        // Position the tooltip above the wrapper
        const rect = wrapper.getBoundingClientRect();
        const tooltipHeight = tooltipDiv.offsetHeight;
        const tooltipWidth  = tooltipDiv.offsetWidth;

        const top  = window.scrollY + rect.top - tooltipHeight - 8; // 8px gap
        const left = window.scrollX + rect.left + (rect.width / 2) - (tooltipWidth / 2);

        tooltipDiv.style.top  = `${top}px`;
        tooltipDiv.style.left = `${left}px`;
    }

    function removeTooltip() {
        if (tooltipDiv) {
            tooltipDiv.remove();
            tooltipDiv = null;
        }
    }
});
