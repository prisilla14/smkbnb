document.addEventListener('DOMContentLoaded', () => {
    const header = document.getElementById('siteHeader');
    const mobileToggle = document.getElementById('mobileToggle');
    const navMenu = document.getElementById('navMenu');
    const backToTop = document.getElementById('backToTop');

    // Sticky shadow & back-to-top button
    window.addEventListener('scroll', () => {
        if (header) {
            header.classList.toggle('scrolled', window.scrollY > 20);
        }
        if (backToTop) {
            backToTop.classList.toggle('visible', window.scrollY > 400);
        }
    });

    if (backToTop) {
        backToTop.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // Mobile drawer toggle
    if (mobileToggle && navMenu) {
        mobileToggle.addEventListener('click', () => {
            const isOpen = navMenu.classList.toggle('open');
            mobileToggle.setAttribute('aria-expanded', String(isOpen));
        });

        navMenu.querySelectorAll('a').forEach((link) => {
            link.addEventListener('click', () => {
                navMenu.classList.remove('open');
                mobileToggle.setAttribute('aria-expanded', 'false');
            });
        });
    }

    // Interactive Article Filter & Search
    const filterButtons = document.querySelectorAll('.filter-btn');
    const searchInput = document.getElementById('articleSearch');
    const articleCards = document.querySelectorAll('.artikel-card[data-category]');
    const noResultsMsg = document.getElementById('noFilterResults');
    let activeCategory = 'semua';

    const applyArticleFilters = () => {
        if (!articleCards.length) return;
        const query = (searchInput ? searchInput.value : '').trim().toLowerCase();
        let visibleCount = 0;

        articleCards.forEach((card) => {
            const cat = card.getAttribute('data-category') || '';
            const text = card.getAttribute('data-search') || '';
            const matchesCategory = activeCategory === 'semua' || cat === activeCategory;
            const matchesSearch = query === '' || text.includes(query);

            if (matchesCategory && matchesSearch) {
                card.style.display = '';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (noResultsMsg) {
            noResultsMsg.hidden = visibleCount > 0;
        }
    };

    filterButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            filterButtons.forEach((b) => b.classList.remove('active'));
            btn.classList.add('active');
            activeCategory = btn.getAttribute('data-filter') || 'semua';
            applyArticleFilters();
        });
    });

    if (searchInput) {
        searchInput.addEventListener('input', applyArticleFilters);
    }

    // Alumni Tabs (Perusahaan Ternama vs Universitas Top 10)
    const tabButtons = document.querySelectorAll('.alumni-tab-btn');
    const tabPanels = document.querySelectorAll('.alumni-panel');

    tabButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-tab');
            tabButtons.forEach((b) => b.classList.remove('active'));
            tabPanels.forEach((p) => p.classList.remove('active'));

            btn.classList.add('active');
            const targetPanel = targetId ? document.getElementById(targetId) : null;
            if (targetPanel) {
                targetPanel.classList.add('active');
            }
        });
    });

    // PPDB & Konsultasi Form Submission via Fetch
    const ppdbForm = document.getElementById('ppdbForm');
    const ppdbFeedback = document.getElementById('ppdbFeedback');
    const ppdbSubmitBtn = document.getElementById('ppdbSubmitBtn');

    if (ppdbForm && ppdbFeedback && ppdbSubmitBtn) {
        ppdbForm.addEventListener('submit', async (event) => {
            event.preventDefault();
            ppdbFeedback.hidden = true;
            ppdbFeedback.className = 'form-feedback';
            ppdbSubmitBtn.disabled = true;
            const originalLabel = ppdbSubmitBtn.textContent;
            ppdbSubmitBtn.textContent = 'Mengirim data...';

            try {
                const formData = new FormData(ppdbForm);
                const response = await fetch(ppdbForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'Accept': 'application/json' }
                });
                const result = await response.json();

                ppdbFeedback.hidden = false;
                if (response.ok && result.ok) {
                    ppdbFeedback.classList.add('success');
                    ppdbFeedback.textContent = result.message;
                    ppdbForm.reset();
                } else {
                    ppdbFeedback.classList.add('error');
                    ppdbFeedback.textContent = result.message || 'Gagal mengirim formulir.';
                }
            } catch (err) {
                ppdbFeedback.hidden = false;
                ppdbFeedback.classList.add('error');
                ppdbFeedback.textContent = 'Tidak dapat terhubung ke server PHP. Pastikan server Apache/PHP berjalan.';
            } finally {
                ppdbSubmitBtn.disabled = false;
                ppdbSubmitBtn.textContent = originalLabel;
            }
        });
    }

    // Portal Login: Role Tab Switcher, Demo Fill & Password Toggle
    const roleTabs = document.querySelectorAll('.role-tab-btn');
    const portalUsername = document.getElementById('portalUsername');
    const portalPassword = document.getElementById('portalPassword');
    const togglePasswordBtn = document.getElementById('togglePasswordBtn');
    const demoFillButtons = document.querySelectorAll('.demo-fill-btn');

    const activateRoleTab = (roleName) => {
        roleTabs.forEach((tab) => {
            const isMatch = tab.getAttribute('data-role-tab') === roleName;
            tab.classList.toggle('active', isMatch);
            if (isMatch && portalUsername) {
                const ph = tab.getAttribute('data-placeholder');
                if (ph) portalUsername.placeholder = ph;
            }
        });
    };

    roleTabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            const roleName = tab.getAttribute('data-role-tab') || 'siswa';
            activateRoleTab(roleName);
        });
    });

    demoFillButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const user = btn.getAttribute('data-user') || '';
            const pass = btn.getAttribute('data-pass') || '';
            const role = btn.getAttribute('data-role') || 'siswa';
            activateRoleTab(role);
            if (portalUsername) portalUsername.value = user;
            if (portalPassword) portalPassword.value = pass;
            if (portalUsername) portalUsername.focus();
        });
    });

    if (togglePasswordBtn && portalPassword) {
        togglePasswordBtn.addEventListener('click', () => {
            const isHidden = portalPassword.type === 'password';
            portalPassword.type = isHidden ? 'text' : 'password';
            togglePasswordBtn.textContent = isHidden ? 'Sembunyikan' : 'Tampilkan';
        });
    }
});
