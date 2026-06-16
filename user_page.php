<?php
session_start();
if (!isset($_SESSION['name'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockFlow - User Dashboard</title>
    
    <!-- Tailwind CSS for styling -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Spline Viewer Component -->
    <script type="module" src="https://unpkg.com/@splinetool/viewer@1.12.94/build/spline-viewer.js"></script>

    <!-- Google Fonts: Inter for clean UI, Cormorant Garamond for elegant serif typography -->
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,650;0,700;1,400&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body, html {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
            background-color: #030205;
        }

        .serif-title {
            font-family: 'Cormorant Garamond', serif;
        }

        /* Fullscreen Spline Background container */
        #canvas-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 1;
            overflow: hidden;
            pointer-events: none;
            transform-style: preserve-3d;
            perspective: 1000px;
        }

        spline-viewer {
            width: 100% !important;
            height: 100% !important;
            display: block;
            animation: cinematicFloat 35s linear infinite;
            transform-origin: center center;
            scale: 1.15;
        }

        @keyframes cinematicFloat {
            0% { transform: rotate(0deg) translateY(0px); }
            50% { transform: rotate(1.5deg) translateY(-10px); }
            100% { transform: rotate(0deg) translateY(0px); }
        }

        /* Unified UI Frame overlay */
        #ui-container {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            pointer-events: none;
        }

        /* Centered dashboard layout frame matching login page scale */
        .master-card {
            pointer-events: auto;
            width: 100%;
            max-width: 600px;
            background: rgba(255, 255, 255, 0.35); /* iOS Frosted Glass Effect */
            backdrop-filter: blur(30px) saturate(180%);
            -webkit-backdrop-filter: blur(30px) saturate(180%);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 2rem;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.85);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4rem 3.5rem;
            color: #ffffff;
            text-align: center;
        }

        .role-badge {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            padding: 6px 18px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            tracking-widest: 0.1em;
            color: rgba(255, 255, 255, 0.9);
            text-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
        }

        /* Secondary Apple-style outlined dark button */
        .logout-btn {
            display: inline-block;
            margin-top: 1.5rem;
            padding: 0.85rem 2.5rem;
            background: #09090b;
            color: #ffffff;
            font-weight: 600;
            font-size: 0.95rem;
            border-radius: 0.75rem;
            transition: all 0.25s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        .logout-btn:hover {
            background: #18181b;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.3);
        }
    </style>
</head>
<body>

    <!-- 3D SPLINE BACKGROUND -->
    <div id="canvas-container">
        <spline-viewer url="https://prod.spline.design/5lhVrjClMBbInbTG/scene.splinecode" loading="eager"></spline-viewer>
    </div>

    <!-- UI Overlay Containing the Dashboard Card -->
    <div id="ui-container">
        <div class="master-card">
            
            <!-- Context Security Level Badge -->
            <span class="role-badge mb-6">User Dashboard Secure Node</span>
            
            <!-- Hello Username Header - Matched to login serif style and font colors -->
            <h1 class="serif-title text-5xl font-normal text-white mb-4 drop-shadow-[0_2px_10px_rgba(0,0,0,0.15)]">
                Hello, <?php echo htmlspecialchars($_SESSION['name']); ?>!
            </h1>
            
            <p class="text-sm text-white/80 font-light max-w-sm leading-relaxed mb-4">
                Welcome to your personal dashboard system node. Monitor secure metrics and active parameters with high-fidelity control flows.
            </p>
            
            <!-- Logout Action Link -->
            <a href="logout.php" class="logout-btn">Disconnect Session</a>
            
        </div>
    </div>

    <!-- SPLINE ENGINE KEEP-ALIVE TRIGGER SCRIPT -->
    <script>
        const viewer = document.querySelector('spline-viewer');
        if (viewer) {
            viewer.addEventListener('load', () => {
                const canvas = viewer.shadowRoot?.querySelector('#canvas3d');
                if (canvas) {
                    let moveToggle = false;
                    setInterval(() => {
                        moveToggle = !moveToggle;
                        const fakeX = moveToggle ? 101 : 100;
                        const event = new MouseEvent('mousemove', {
                            clientX: fakeX,
                            clientY: 100,
                            bubbles: true
                        });
                        canvas.dispatchEvent(event);
                    }, 500);
                }
            });
        }
    </script>
</body>
</html>