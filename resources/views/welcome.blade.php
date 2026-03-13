<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kanban COOSAE - Kelola Proyek Jadi Mudah</title>
    <!-- Font Awesome 6 (free) for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(145deg, #f9fafc 0%, #f1f4f9 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: #1e293b;
            line-height: 1.5;
        }

        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
            width: 100%;
        }

        /* Header & Navigation */
        header {
            background-color: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.02);
            border-bottom: 1px solid rgba(226, 232, 240, 0.6);
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 80px;
        }

        .logo a {
            font-size: 1.8rem;
            font-weight: 700;
            letter-spacing: -0.5px;
            text-decoration: none;
            background: linear-gradient(135deg, #2563eb, #7c3aed);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }

        .nav-links {
            display: flex;
            gap: 2rem;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: #334155;
            font-weight: 500;
            transition: color 0.2s;
            font-size: 1rem;
        }

        .nav-links a:hover {
            color: #2563eb;
        }

        .btn-login {
            background: #1e293b;
            color: white !important;
            padding: 10px 22px;
            border-radius: 40px;
            font-weight: 600;
            margin-left: 8px;
            transition: background 0.2s, transform 0.1s;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
        }

        .btn-login:hover {
            background: #0f172a;
            color: white !important;
            transform: scale(1.02);
        }

        /* Main content */
        main {
            flex: 1;
            padding: 60px 0 80px;
        }

        /* Hero section / main info about Kanban */
        .hero {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            margin-bottom: 4rem;
        }

        .hero h1 {
            font-size: 3.2rem;
            font-weight: 800;
            line-height: 1.2;
            max-width: 800px;
            background: linear-gradient(145deg, #0f172a, #2563eb);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            margin-bottom: 1.5rem;
        }

        .hero p {
            font-size: 1.25rem;
            color: #475569;
            max-width: 700px;
            margin-bottom: 2rem;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            justify-content: center;
        }

        .btn-primary {
            background: #2563eb;
            color: white;
            text-decoration: none;
            padding: 14px 36px;
            border-radius: 60px;
            font-weight: 600;
            font-size: 1.1rem;
            transition: background 0.2s, transform 0.2s;
            box-shadow: 0 10px 20px -8px rgba(37, 99, 235, 0.4);
        }

        .btn-primary:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            color: #1e293b;
            text-decoration: none;
            padding: 12px 32px;
            border-radius: 60px;
            font-weight: 600;
            font-size: 1.1rem;
            border: 2px solid #cbd5e1;
            transition: background 0.2s, border-color 0.2s;
        }

        .btn-outline:hover {
            background: #ffffff;
            border-color: #2563eb;
        }

        /* Info cards section */
        .info-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 2rem;
            margin: 5rem 0 3rem;
        }

        .card {
            background: white;
            border-radius: 32px;
            padding: 2rem 1.8rem;
            box-shadow: 0 15px 30px -12px rgba(0, 0, 0, 0.1);
            transition: transform 0.25s, box-shadow 0.3s;
            border: 1px solid rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(4px);
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 30px 40px -15px rgba(37, 99, 235, 0.25);
        }

        .card-icon {
            font-size: 2.8rem;
            margin-bottom: 1.2rem;
            color: #2563eb;
        }

        .card h3 {
            font-size: 1.7rem;
            margin-bottom: 1rem;
            font-weight: 600;
        }

        .card p {
            color: #475569;
            margin-bottom: 1.8rem;
        }

        .feature-list {
            list-style: none;
        }

        .feature-list li {
            margin-bottom: 0.7rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .feature-list i {
            color: #2563eb;
            font-size: 1rem;
            width: 20px;
        }

        /* About section (extra) */
        .about-grid {
            display: flex;
            flex-wrap: wrap;
            background: white;
            border-radius: 40px;
            overflow: hidden;
            box-shadow: 0 25px 40px -18px #1e293b;
            margin: 5rem 0;
        }

        .about-text {
            flex: 1 1 300px;
            padding: 3rem;
        }

        .about-text h2 {
            font-size: 2.4rem;
            margin-bottom: 1.5rem;
            font-weight: 700;
        }

        .about-text p {
            color: #334155;
            font-size: 1.1rem;
            max-width: 500px;
        }

        .about-preview {
            flex: 1 1 300px;
            background: linear-gradient(125deg, #dbeafe, #ede9fe);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .kanban-mini {
            background: white;
            border-radius: 28px;
            padding: 1.8rem 1.5rem;
            width: 100%;
            max-width: 320px;
            box-shadow: 0 25px 30px -12px rgba(0, 0, 0, 0.2);
        }

        .mini-columns {
            display: flex;
            gap: 0.8rem;
            justify-content: space-between;
        }

        .mini-col {
            background: #f1f5f9;
            border-radius: 16px;
            padding: 0.8rem 0.5rem;
            width: 30%;
            text-align: center;
            font-size: 0.75rem;
            font-weight: 600;
            color: #334155;
        }

        .mini-card {
            background: white;
            border-radius: 10px;
            padding: 0.5rem 0.3rem;
            margin-top: 0.6rem;
            font-size: 0.6rem;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
            border-left: 3px solid #2563eb;
        }

        /* Footer */
        footer {
            background: #0f172a;
            color: #e2e8f0;
            padding: 48px 0 24px;
            border-top: 1px solid #1e293b;
        }

        .footer-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            gap: 40px;
            margin-bottom: 40px;
        }

        .footer-brand {
            flex: 2 1 260px;
        }

        .footer-brand h3 {
            color: white;
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 12px;
            letter-spacing: -0.5px;
        }

        .footer-brand p {
            color: #94a3b8;
            margin: 16px 0;
            max-width: 300px;
        }

        .github-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #1e293b;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 40px;
            font-size: 0.95rem;
            border: 1px solid #334155;
            transition: background 0.2s;
        }

        .github-link:hover {
            background: #334155;
        }

        .footer-links {
            flex: 1 1 160px;
        }

        .footer-links h4 {
            color: white;
            font-size: 1.2rem;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .footer-links ul {
            list-style: none;
        }

        .footer-links li {
            margin-bottom: 12px;
        }

        .footer-links a {
            color: #94a3b8;
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-links a:hover {
            color: white;
        }

        .footer-bottom {
            text-align: center;
            padding-top: 28px;
            border-top: 1px solid #1e293b;
            color: #64748b;
            font-size: 0.95rem;
        }

        /* responsive */
        @media (max-width: 700px) {
            .navbar {
                flex-direction: column;
                height: auto;
                padding: 16px 0;
                gap: 12px;
            }

            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 1rem 1.5rem;
            }

            .hero h1 {
                font-size: 2.4rem;
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="container navbar">
            <div class="logo">
                <a href="/">Kanban<span style="color:#1e293b;"> </span>COOSAE</a>
            </div>
            <div class="nav-links">
                <a href="#home">Home</a>
                <a href="#about">About</a>
                <a href="#contact">Contact Us</a>
                <a href="{{ url('/app/login') }}" class="btn-login"><i class="fas fa-sign-in-alt"
                        style="margin-right: 6px;"></i>Login</a>
            </div>
        </div>
    </header>

    <main>
        <div class="container">
            <!-- HOME SECTION -->
            <section id="home" class="hero">
                <h1>Kelola alur kerja tim dengan sistem Kanban yang intuitif</h1>
                <p>Kanban membantu Anda memvisualisasikan tugas, membatasi pekerjaan berjalan, dan memaksimalkan
                    efisiensi—semua dalam satu papan yang fleksibel.</p>
                <div class="cta-buttons">
                    <a href="{{ url('/app/login') }}" class="btn-primary"><i class="fas fa-hand-pointer"
                            style="margin-right: 8px;"></i>Mulai sekarang</a>
                    <a href="#about" class="btn-outline">Pelajari lebih lanjut</a>
                </div>
            </section>

            <!-- INFORMASI KANBAN (cards) -->
            <div class="info-section">
                <div class="card">
                    <div class="card-icon"><i class="fas fa-columns"></i></div>
                    <h3>Apa itu Kanban?</h3>
                    <p>Metode visual untuk mengelola pekerjaan. Setiap tugas berjalan sebagai kartu dalam kolom yang
                        melambangkan tahapan proses — dari "To Do" hingga "Done".</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i> Transparansi alur kerja</li>
                        <li><i class="fas fa-check-circle"></i> Batasan WIP (Work in Progress)</li>
                        <li><i class="fas fa-check-circle"></i> Perbaikan berkelanjutan</li>
                    </ul>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fas fa-users"></i></div>
                    <h3>Untuk Tim Hebat</h3>
                    <p>Baik Anda tim developer, marketing, atau HR, papan Kanban menyesuaikan dengan gaya kerja
                        kolaboratif dan cepat.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i> Kolaborasi real-time</li>
                        <li><i class="fas fa-check-circle"></i> Notifikasi & assignment</li>
                        <li><i class="fas fa-check-circle"></i> Lampiran & komentar</li>
                    </ul>
                </div>
                <div class="card">
                    <div class="card-icon"><i class="fas fa-chart-line"></i></div>
                    <h3>Analitik & Metrik</h3>
                    <p>Lacak cycle time, throughput, dan kinerja tim dengan diagram yang mudah dipahami. Ambil keputusan
                        berdasarkan data.</p>
                    <ul class="feature-list">
                        <li><i class="fas fa-check-circle"></i> Diagram kumulatif</li>
                        <li><i class="fas fa-check-circle"></i> Laporan beban kerja</li>
                        <li><i class="fas fa-check-circle"></i> Ekspor data</li>
                    </ul>
                </div>
            </div>

            <!-- ABOUT SECTION (lebih detail) -->
            <section id="about" class="about-grid">
                <div class="about-text">
                    <h2>Mengapa memilih Kanban?</h2>
                    <p>Kami membangun aplikasi ini dengan Laravel & Filament, menggabungkan kemudahan penggunaan dengan
                        fleksibilitas enterprise. Tidak perlu setup rumit — langsung undang tim dan mulai berkolaborasi.
                    </p>
                    <br>
                    <p><i class="fas fa-check-circle" style="color:#2563eb; margin-right: 8px;"></i> 100% gratis untuk
                        tim kecil</p>
                    <p><i class="fas fa-check-circle" style="color:#2563eb; margin-right: 8px;"></i> Integrasi dengan
                        Google Calendar & Slack (segera)</p>
                    <p><i class="fas fa-check-circle" style="color:#2563eb; margin-right: 8px;"></i> Tersedia
                        self-hosted version</p>
                </div>
                <div class="about-preview">
                    <div class="kanban-mini">
                        <div class="mini-columns">
                            <div class="mini-col">To Do
                                <div class="mini-card">Desain UI</div>
                                <div class="mini-card">Tulis docs</div>
                            </div>
                            <div class="mini-col">In Progress
                                <div class="mini-card">Backend API</div>
                            </div>
                            <div class="mini-col">Done
                                <div class="mini-card">Meeting</div>
                            </div>
                        </div>
                        <p style="text-align:center; margin-top:1rem; font-size:0.7rem; color:#2563eb;"><i
                                class="fas fa-arrows-alt"></i> Drag & drop langsung</p>
                    </div>
                </div>
            </section>

            <!-- CONTACT US SECTION -->
            <section id="contact" style="margin: 5rem 0 2rem; text-align: center;">
                <h2 style="font-size: 2.4rem; margin-bottom: 1rem;">Hubungi Kami</h2>
                <p style="color: #475569; max-width: 500px; margin: 0 auto 2rem;">Punya pertanyaan atau butuh bantuan?
                </p>
                <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 3rem;">
                    <div style="display: flex; flex-direction: column; align-items: center;">
                        <i class="fas fa-envelope" style="font-size: 2.2rem; color:#2563eb; margin-bottom: 8px;"></i>
                        <span style="font-weight:600;">moanshori@itsk-soepraoen.ac.id</span>
                    </div>
                    <div style="display: flex; flex-direction: column; align-items: center;">
                        <i class="fab fa-github" style="font-size: 2.2rem; color:#2563eb; margin-bottom: 8px;"></i>
                        <span style="font-weight:600;">Kanban COOSAE</span>
                    </div>
                </div>
            </section>
        </div>
    </main>

    <footer>
        <div class="container">
            <div class="footer-content">
                <div class="footer-brand">
                    <h3>Kanban COOSAE</h3>
                    <p>Alat manajemen proyek visual yang sederhana namun kuat. Dikembangkan dengan cinta menggunakan
                        Laravel & Filament.</p>
                    <a href="https://github.com/Aden-Aghsal/filament-kanban" class="github-link"><i
                            class="fab fa-github"></i>Kanban-Coosae</a>
                </div>
                <div class="footer-links">
                    <h4>Menu</h4>
                    <ul>
                        <li><a href="#home">Home</a></li>
                        <li><a href="#about">About</a></li>
                        <li><a href="#contact">Contact Us</a></li>
                        <li><a href="{{ url('/app/login') }}">Login</a></li>
                    </ul>
                </div>
                <div class="footer-links">
                    <h4>Pengembang</h4>
                    <ul>
                        <li><a href="https://github.com/Aden-Aghsal">Ardhan Aghsal D. P.</a></li>
                        <li><a href="https://github.com/moanshori-zerone">Mochammad Anshori</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>© 2026 Kanban COOSAE. Hak cipta dilindungi. Dibuat untuk produktivitas.</p>
            </div>
        </div>
    </footer>

    <!-- Smooth scroll untuk anchor (opsional) -->
    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            });
        });
    </script>
</body>

</html>
