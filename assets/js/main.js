// ===== Back to Top Button =====
const backToTop = document.getElementById("back-to-top");
if (backToTop) {
    window.addEventListener("scroll", () => {
        backToTop.style.display = window.scrollY > 300 ? "block" : "none";
    });
    backToTop.addEventListener("click", () => {
        window.scrollTo({ top: 0, behavior: "smooth" });
    });
}

// ===== Live AJAX Search for Books =====
async function searchBooks() {
    const input = document.getElementById("searchInput");
    const query = input.value.trim().toLowerCase();
    const grid = document.querySelector(".book-grid");

    if (!grid) return;

    if (query === "") {
        // If search is empty, reload the page or show default categories
        window.location.reload();
        return;
    }

    try {
        const res = await fetch(`search_books.php?q=${encodeURIComponent(query)}`);
        const books = await res.json();

        // Clear current grid
        grid.innerHTML = "";

        if (books.length === 0) {
            grid.innerHTML = `<p style="grid-column:1/-1; text-align:center;">No books found.</p>`;
            return;
        }

        // Render books dynamically
        books.forEach(book => {
            const div = document.createElement("div");
            div.className = "book-card";
            div.innerHTML = `
                <div class="book-img-wrap">
                    <img src="${book.image ? 'uploads/' + encodeURIComponent(book.image) : 'assets/images/placeholder.png'}" alt="${book.title}">
                </div>
                <div class="book-info">
                    <h3 class="book-title">${book.title}</h3>
                    <div class="book-card-footer">
                        <span class="book-price">${book.price}</span>
                        <div class="action-btns">
                            <a href="book_detail.php?id=${book.id}" class="btn small">View</a>
                        </div>
                    </div>
                </div>
            `;
            grid.appendChild(div);
        });
    } catch (err) {
        console.error("Search error:", err);
    }
}

// ===== Preview Image Before Upload =====
function previewImage(input, previewId) {
    const file = input.files[0];
    if (!file) return;

    const img = document.getElementById(previewId);
    img.src = URL.createObjectURL(file);
    img.style.display = "block";
}

// ===== Tooltip Init =====
document.querySelectorAll('[data-toggle="tooltip"]').forEach(el => {
    el.addEventListener('mouseenter', () => {
        const title = el.getAttribute('title');
        const tooltip = document.createElement('div');
        tooltip.className = 'tooltip';
        tooltip.innerText = title;
        document.body.appendChild(tooltip);

        const rect = el.getBoundingClientRect();
        tooltip.style.left = rect.left + 'px';
        tooltip.style.top = rect.top - 30 + 'px';

        el.addEventListener('mouseleave', () => tooltip.remove());
    });
});

// ===== Light / dark theme =====
function rereadIsDarkTheme() {
    return document.documentElement.getAttribute('data-theme') === 'dark';
}

function rereadSyncThemeUi() {
    const dark = rereadIsDarkTheme();
    document.querySelectorAll('[data-set-theme]').forEach((btn) => {
        const mode = btn.getAttribute('data-set-theme');
        const on = (mode === 'dark') === dark;
        btn.classList.toggle('is-active', on);
        btn.setAttribute('aria-pressed', on ? 'true' : 'false');
    });
}

function rereadInitThemeControls() {
    document.querySelectorAll('[data-set-theme]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const mode = btn.getAttribute('data-set-theme');
            if (mode === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
                try {
                    localStorage.setItem('reread-theme', 'dark');
                } catch (e) {}
            } else {
                document.documentElement.removeAttribute('data-theme');
                try {
                    localStorage.setItem('reread-theme', 'light');
                } catch (e) {}
            }
            rereadSyncThemeUi();
        });
    });
    rereadSyncThemeUi();

    const mq = window.matchMedia('(prefers-color-scheme: dark)');
    mq.addEventListener('change', () => {
        try {
            if (localStorage.getItem('reread-theme')) {
                return;
            }
        } catch (e) {
            return;
        }
        if (mq.matches) {
            document.documentElement.setAttribute('data-theme', 'dark');
        } else {
            document.documentElement.removeAttribute('data-theme');
        }
        rereadSyncThemeUi();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', rereadInitThemeControls);
} else {
    rereadInitThemeControls();
}
