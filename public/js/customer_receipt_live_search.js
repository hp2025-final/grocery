document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.querySelector('#live-search-input');
    const tableContainer = document.querySelector('#live-receipts-table');
    let timeout = null;

    if (searchInput && tableContainer) {
        searchInput.addEventListener('input', function () {
            clearTimeout(timeout);
            timeout = setTimeout(function () {
                const query = searchInput.value;
                const url = new URL(window.location.origin + '/customer-receipts/live-search');
                url.searchParams.set('search', query);
                fetch(url)
                    .then(response => response.text())
                    .then(html => {
                        tableContainer.innerHTML = html;
                    });
            }, 300);
        });
    }
});
