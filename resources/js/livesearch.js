$(document).ready(function () {
    let searchTimeout;
    const searchBox = $('#navbarSearchBox');
    const searchResults = $('#navbarSearchResults');

    // Fungsi untuk menampilkan hasil pencarian
    function showSearchResults(query) {
        if (query.length > 0) {
            $.ajax({
                url: window.location.href,
                method: 'POST',
                data: { query: query },
                beforeSend: function() {
                    searchResults.html('<div class="search-loading">Mencari</div>').show();
                },
                success: function (response) {
                    if (response.trim() === '') {
                        searchResults.html('<div class="search-result-item no-results">Tidak ada produk yang ditemukan</div>').show();
                    } else {
                        searchResults.html(response).show();
                    }
                },
                error: function() {
                    searchResults.html('<div class="search-result-item no-results">Terjadi kesalahan saat mencari</div>').show();
                }
            });
        } else {
            searchResults.empty().hide();
        }
    }

    // Event handler untuk input pencarian
    searchBox.on('keyup', function () {
        clearTimeout(searchTimeout);
        const query = $(this).val().trim();
        
        searchTimeout = setTimeout(function() {
            showSearchResults(query);
        }, 300);
    });

    // Event handler untuk fokus pada search box
    searchBox.on('focus', function() {
        const query = $(this).val().trim();
        if (query.length > 0) {
            showSearchResults(query);
        }
    });

    // Event handler untuk klik di luar area pencarian
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.search-bar').length) {
            searchResults.hide();
        }
    });

    // Event handler untuk klik pada hasil pencarian
    $(document).on('click', '.search-result-item a', function() {
        searchResults.hide();
    });
});
