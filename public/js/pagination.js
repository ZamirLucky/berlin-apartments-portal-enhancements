// /public/js/pagination.js
/* Call this class in EmailSmartlockView and AccountsView after the table is rendered */
export class Paginator {
    constructor({
        rows,
        paginationControls
    }) {
        this.rows = rows;
        this.rowsPerPage = 25;
        this.paginationControls = paginationControls;
        this.totalPages = Math.max(1, Math.ceil(this.rows.length / this.rowsPerPage));
        this.currentPage = 1;
        this.init(1);
    }

    // initialize pagination
    init(page) {
        this.currentPage = Math.min(Math.max(1, page), this.totalPages);
        const start = (this.currentPage - 1) * this.rowsPerPage;
        const end = this.currentPage * this.rowsPerPage;
        this.rows.forEach((tr, i) => {
            const inPage = i >= start && i < end;
            tr.style.display = inPage ? '' : 'none';
        });

        this.renderPaginationControls();
    }

    // create pagination controls
    renderPaginationControls() {
        if (!this.paginationControls) return;
        this.paginationControls.innerHTML = '';

        // Show page info
        const pageInfo = document.createElement("span");
        pageInfo.classList.add("me-3");
        pageInfo.textContent = `Page ${this.currentPage} of ${this.totalPages}`;

        this.paginationControls.appendChild(pageInfo);

        const prev = document.createElement('button');
        prev.textContent = 'Previous';
        prev.className = 'btn btn-primary me-2';
        prev.disabled = this.currentPage === 1;
        prev.onclick = () => this.init(this.currentPage - 1);

        const next = document.createElement('button');
        next.textContent = 'Next';
        next.className = 'btn btn-primary';
        next.disabled = this.currentPage === this.totalPages;
        next.onclick = () => this.init(this.currentPage + 1);

        this.paginationControls.append(prev, next);
    }
}
