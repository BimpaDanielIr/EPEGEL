document.addEventListener('DOMContentLoaded', () => {
    // --- Application State ---
    const App = {
        currentUser: null,
        charts: {},

        // --- Initialization ---
        init() {
            this.ui = {
                loginPage: document.getElementById('loginPage'),
                mainApp: document.getElementById('mainApp'),
                loginForm: document.getElementById('loginForm'),
                registerForm: document.getElementById('registerForm'),
                showRegisterBtn: document.getElementById('showRegister'),
                showLoginBtn: document.getElementById('showLogin'),
                logoutBtn: document.getElementById('logoutBtn'),
                userNameSpan: document.getElementById('userName'),
                pageTitle: document.getElementById('pageTitle'),
                adminMenu: document.getElementById('adminMenu'),
                studentMenu: document.getElementById('studentMenu'),
                contentArea: document.getElementById('content-area'),
                
                // Headers
                studentHeader: document.getElementById('studentHeader'),
                adminHeader: document.getElementById('adminHeader'),
                studentAvatar: document.getElementById('studentAvatar'),
                studentHeaderName: document.getElementById('studentHeaderName'),
                studentHeaderClasse: document.getElementById('studentHeaderClasse'),
                studentHeaderMatricule: document.getElementById('studentHeaderMatricule'),
                adminAvatar: document.getElementById('adminAvatar'),
                adminHeaderName: document.getElementById('adminHeaderName'),
                adminHeaderRole: document.getElementById('adminHeaderRole'),

                // Modal
                modal: document.getElementById('mainModal'),
                modalTitle: document.getElementById('modalTitle'),
                modalForm: document.getElementById('modalForm'),
                closeModalBtn: document.getElementById('closeModalBtn'),
                toast: document.getElementById('toast'),
            };

            this.addEventListeners();
            this.checkSession();
        },

        // --- Event Listeners ---
        addEventListeners() {
            this.ui.loginForm?.addEventListener('submit', this.handleLogin.bind(this));
            this.ui.registerForm?.addEventListener('submit', this.handleRegister.bind(this));
            this.ui.logoutBtn?.addEventListener('click', this.handleLogout.bind(this));
            this.ui.showRegisterBtn?.addEventListener('click', () => this.toggleAuthForm('register'));
            this.ui.showLoginBtn?.addEventListener('click', () => this.toggleAuthForm('login'));
            this.ui.closeModalBtn?.addEventListener('click', this.closeModal.bind(this));

            document.querySelectorAll('.nav-link').forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.loadContent(e.currentTarget.dataset.page);
                });
            });
        },

        // --- Core App Logic ---
        async checkSession() {
            const data = await this.apiCall('check_session.php');
            if (data && data.isLoggedIn) {
                this.currentUser = data.user;
                this.showMainApp();
            } else {
                this.showLoginPage();
            }
        },

        showLoginPage() {
            this.ui.loginPage.style.display = 'flex';
            this.ui.mainApp.style.display = 'none';
        },

        showMainApp() {
            this.ui.loginPage.style.display = 'none';
            this.ui.mainApp.style.display = 'flex';
            this.ui.userNameSpan.textContent = this.currentUser.name;
            this.ui.adminMenu.style.display = (this.currentUser.role === 'administrateur') ? 'block' : 'none';
            this.ui.studentMenu.style.display = (this.currentUser.role === 'etudiant') ? 'block' : 'none';
            
            this.updateHeader();
            const initialPage = this.currentUser.role === 'administrateur' ? 'dashboard' : 'dashboard-student';
            this.loadContent(initialPage);
        },

        async updateHeader() {
            if (this.currentUser.role === 'administrateur') {
                this.ui.adminHeader.style.display = 'flex';
                this.ui.studentHeader.style.display = 'none';
                const data = await this.apiCall('admin_data.php?action=admin_info');
                if (data && data.success) {
                    const info = data.info;
                    this.ui.adminHeaderName.textContent = `${info.nom} ${info.prenom}`;
                    this.ui.adminHeaderRole.textContent = info.role.charAt(0).toUpperCase() + info.role.slice(1);
                    this.ui.adminAvatar.src = `https://ui-avatars.com/api/?name=${info.nom}+${info.prenom}&background=random`;
                }
            } else if (this.currentUser.role === 'etudiant') {
                this.ui.studentHeader.style.display = 'flex';
                this.ui.adminHeader.style.display = 'none';
                const data = await this.apiCall('get_student_data.php?action=dashboard_stats');
                if (data && data.success) {
                    const stats = data.stats;
                    this.ui.studentHeaderName.textContent = `${stats.nom} ${stats.prenom}`;
                    this.ui.studentHeaderClasse.textContent = `Classe: ${stats.classe || '-'}`;
                    this.ui.studentHeaderMatricule.textContent = `Matricule: ${stats.matricule || '-'}`;
                    this.ui.studentAvatar.src = `https://ui-avatars.com/api/?name=${stats.nom}+${stats.prenom}&background=random`;
                }
            }
        },

        // --- Content Loading Router ---
        loadContent(page) {
            this.ui.contentArea.innerHTML = ''; // Clear previous content

            switch (page) {
                case 'dashboard':
                    this.ui.pageTitle.textContent = "Tableau de Bord";
                    this.loadAdminDashboard();
                    break;
                case 'dashboard-student':
                    this.ui.pageTitle.textContent = "Mon Tableau de Bord";
                    this.loadStudentDashboard();
                    break;
                // Add other pages here
                default:
                    this.ui.pageTitle.textContent = "Page non trouvée";
                    this.ui.contentArea.innerHTML = `<p>Le contenu pour "${page}" n'est pas encore implémenté.</p>`;
                    break;
            }
        },

        // --- Admin Dashboard ---
        async loadAdminDashboard() {
            // Create the HTML structure for the admin dashboard
            this.ui.contentArea.innerHTML = `
                <h3 class="text-2xl font-semibold mb-4">Statistiques Générales</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6"><h4 class="font-bold mb-2">Étudiants</h4><p id="adminStudentsCount"></p></div>
                    <div class="bg-white rounded-lg shadow p-6"><h4 class="font-bold mb-2">Enseignants</h4><p id="adminTeachersCount"></p></div>
                    <div class="bg-white rounded-lg shadow p-6"><h4 class="font-bold mb-2">Cours</h4><p id="adminCoursesCount"></p></div>
                    <div class="bg-white rounded-lg shadow p-6"><h4 class="font-bold mb-2">Recettes</h4><p id="adminRevenue"></p></div>
                </div>
                <h4 class="font-bold mb-2">Liste des étudiants</h4>
                <button id="addStudentBtn" class="bg-green-600 text-white px-4 py-2 rounded mb-4">Ajouter un étudiant</button>
                <table class="min-w-full divide-y divide-gray-200 mb-8">
                    <thead class="bg-gray-50"><tr><th class="px-6 py-3">Matricule</th><th class="px-6 py-3">Nom</th><th class="px-6 py-3">Classe</th><th class="px-6 py-3">Statut</th><th class="px-6 py-3">Actions</th></tr></thead>
                    <tbody id="adminStudentsTableBody" class="bg-white divide-y divide-gray-200"></tbody>
                </table>
            `;

            // Fetch and populate stats
            const stats = await this.apiCall('admin_data.php?action=stats');
            if (stats && stats.success) {
                document.getElementById('adminStudentsCount').textContent = stats.stats.students;
                document.getElementById('adminTeachersCount').textContent = stats.stats.teachers;
                document.getElementById('adminCoursesCount').textContent = stats.stats.courses;
                document.getElementById('adminRevenue').textContent = `${stats.stats.revenue || 0} FCFA`;
            }

            // Fetch and populate students list
            const students = await this.apiCall('admin_students.php?action=list');
            if (students && students.success) {
                const tableBody = document.getElementById('adminStudentsTableBody');
                tableBody.innerHTML = '';
                students.students.forEach(s => {
                    const row = tableBody.insertRow();
                    row.setAttribute('data-id', s.id_utilisateur);
                    row.innerHTML = `
                        <td class="border px-4 py-2">${s.numero_etudiant}</td>
                        <td class="border px-4 py-2">${s.nom} ${s.prenom}</td>
                        <td class="border px-4 py-2">${s.classe}</td>
                        <td class="border px-4 py-2">${s.est_actif ? 'Actif' : 'Inactif'}</td>
                        <td class="border px-4 py-2">
                            <a href="generate_bulletin.php?student_id=${s.id_utilisateur}" target="_blank" class="text-green-600 p-1" title="Générer Bulletin"><i class="fas fa-file-pdf"></i></a>
                            <button class="text-yellow-600 p-1" title="Modifier"><i class="fas fa-edit"></i></button>
                            <button class="text-red-600 p-1" title="Supprimer"><i class="fas fa-trash"></i></button>
                        </td>`;
                });
            }
        },

        // --- Student Dashboard ---
        async loadStudentDashboard() {
            // Create the HTML structure for the student dashboard
            this.ui.contentArea.innerHTML = `
                <h3 class="text-2xl font-semibold mb-4">Bienvenue, <span id="studentName"></span></h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h4 class="font-bold mb-2">Classe</h4><p id="studentClass"></p>
                        <h4 class="font-bold mt-4 mb-2">Année scolaire</h4><p id="studentYear"></p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h4 class="font-bold mb-2">Situation financière</h4>
                        <p>Total à payer : <span id="totalFees"></span> FCFA</p>
                        <p>Payé : <span id="paidFees"></span> FCFA</p>
                        <p>Reste : <span id="remainingFees"></span> FCFA</p>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h4 class="font-bold mb-2">Moyenne générale</h4><p id="averageGrade"></p>
                    </div>
                </div>
            `;

            // Fetch and populate data
            const data = await this.apiCall('get_student_data.php?action=dashboard_stats');
            if (data && data.success) {
                const stats = data.stats;
                document.getElementById('studentName').textContent = this.currentUser.name;
                document.getElementById('studentClass').textContent = stats.classe || '-';
                document.getElementById('studentYear').textContent = stats.annee_scolaire || '-';
                document.getElementById('totalFees').textContent = stats.montant_total || '0';
                document.getElementById('paidFees').textContent = stats.montant_paye || '0';
                document.getElementById('remainingFees').textContent = stats.solde_du || '0';
                document.getElementById('averageGrade').textContent = stats.moyenne_generale || 'N/A';
            }
        },

        // --- Authentication ---
        async handleLogin(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            const response = await this.apiCall('login.php', { method: 'POST', body: data });
            if (response && response.success) {
                this.currentUser = response.user;
                this.showMainApp();
            } else {
                this.showToast(response ? response.message : 'Erreur inconnue', 'error');
            }
        },

        async handleRegister(e) {
            e.preventDefault();
            const formData = new FormData(e.target);
            const data = Object.fromEntries(formData.entries());
            const response = await this.apiCall('register.php', { method: 'POST', body: data });
            if (response && response.success) {
                this.showToast('Inscription réussie. Votre matricule est: ' + response.matricule, 'success');
                this.toggleAuthForm('login');
                this.ui.registerForm.reset();
            } else {
                this.showToast(response ? response.message : 'Erreur lors de l\'inscription.', 'error');
            }
        },

        async handleLogout() {
            await this.apiCall('logout.php');
            this.currentUser = null;
            this.showLoginPage();
            this.showToast('Déconnexion réussie.', 'info');
        },

        toggleAuthForm(type) {
            this.ui.loginForm.style.display = (type === 'login') ? 'block' : 'none';
            this.ui.registerForm.style.display = (type === 'register') ? 'block' : 'none';
        },

        // --- Utilities ---
        async apiCall(url, options = {}) {
            try {
                const defaultOptions = {
                    method: 'GET',
                    headers: { 'Content-Type': 'application/json' },
                };
                if (options.body && typeof options.body !== 'string') {
                    options.body = JSON.stringify(options.body);
                }
                const response = await fetch(url, { ...defaultOptions, ...options });
                if (!response.ok) throw new Error(`Erreur HTTP: ${response.status}`);
                return await response.json();
            } catch (error) {
                console.error('Erreur API:', error);
                this.showToast('Erreur de communication avec le serveur.', 'error');
                return null;
            }
        },

        showToast(message, type = 'info') {
            this.ui.toast.textContent = message;
            this.ui.toast.className = `toast show ${type}`;
            setTimeout(() => this.ui.toast.classList.remove('show'), 3000);
        },

        openModal(title, formHtml, onSubmit) {
            this.ui.modalTitle.textContent = title;
            this.ui.modalForm.innerHTML = formHtml;
            this.ui.modal.classList.add('active');
            this.ui.modalForm.onsubmit = (e) => {
                e.preventDefault();
                const formData = Object.fromEntries(new FormData(e.target));
                onSubmit(formData);
                this.closeModal();
            };
        },

        closeModal() {
            this.ui.modal.classList.remove('active');
        }
    };

    App.init();
});