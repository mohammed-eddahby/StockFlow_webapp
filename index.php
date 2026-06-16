<?php
session_start();
// Determine which form to show (default to login)
$active_form = isset($_SESSION['active_form']) ? $_SESSION['active_form'] : 'login';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StockFlow - Login & Registration</title>
    
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
            pointer-events: none; /* Prevents user dragging from breaking the layout */
            transform-style: preserve-3d;
            perspective: 1000px;
        }

        spline-viewer {
            width: 100% !important;
            height: 100% !important;
            display: block;
            /* Infinite fallback cinematic animation in case internal Spline logic sleeps */
            animation: cinematicFloat 35s linear infinite;
            transform-origin: center center;
            scale: 1.15; /* Prevents empty edges during continuous visual rotation */
        }

        /* Continuous micro-rotation fallback loop to guarantee movement if Spline freezes */
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

        /* The master split-panel card matching the image proportions */
        .master-card {
            pointer-events: auto;
            width: 100%;
            max-width: 1120px;
            height: 100%;
            max-height: 780px;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 2rem;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.85);
            display: flex;
            overflow: hidden;
        }

        /* Left panel - completely transparent with subtle edge separator */
        .art-side {
            width: 46%;
            height: 100%;
            position: relative;
            background: transparent; 
            backdrop-filter: none; 
            -webkit-backdrop-filter: none;
            border-right: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 4rem 3.5rem;
        }

        /* Right panel - Premium iOS High-Fidelity Frosted Glass Effect */
        .form-side {
            width: 54%;
            height: 100%;
            background: rgba(255, 255, 255, 0.35); 
            backdrop-filter: blur(30px) saturate(180%); 
            -webkit-backdrop-filter: blur(30px) saturate(180%);
            border-left: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 4rem 5rem;
            color: #ffffff; 
            overflow: hidden; 
            perspective: 1500px; 
        }

        /* CSS Grid Overlapping Wrapper */
        .form-container-wrapper {
            position: relative;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr;
            grid-template-rows: 1fr;
            align-items: center;
            flex-grow: 1;
            transform-style: preserve-3d;
        }

        /* The 3D Book Page style */
        .sliding-form {
            grid-area: 1 / 1 / 2 / 2; 
            width: 100%;
            transform-origin: left center; 
            backface-visibility: hidden; 
            -webkit-backface-visibility: hidden;
            transition: transform 0.8s cubic-bezier(0.2, 0.8, 0.2, 1), 
                        opacity 0.8s cubic-bezier(0.2, 0.8, 0.2, 1), 
                        filter 0.8s cubic-bezier(0.2, 0.8, 0.2, 1);
            will-change: transform, opacity, filter;
        }

        /* Form Visibility and 3D Rotation states */
        .form-visible {
            opacity: 1;
            transform: rotateY(0deg);
            pointer-events: auto;
            filter: blur(0px);
            z-index: 2;
        }

        /* Left-sliding exit */
        .form-hidden-left {
            opacity: 0;
            transform: rotateY(-95deg); 
            pointer-events: none;
            filter: blur(4px);
            z-index: 1;
        }

        /* Right-sliding exit */
        .form-hidden-right {
            opacity: 0;
            transform: rotateY(95deg); 
            pointer-events: none;
            filter: blur(4px);
            z-index: 1;
        }

        /* Form headings color treatment */
        .form-side h3 {
            color: #ffffff;
            text-shadow: 0 2px 10px rgba(0, 0, 0, 0.15);
        }

        .form-side p {
            color: rgba(255, 255, 255, 0.8);
        }

        .form-label {
            color: rgba(255, 255, 255, 0.9);
            text-shadow: 0 1px 4px rgba(0,0,0,0.15);
        }

        /* iOS Frosted Glass Input style */
        .clean-input {
            width: 100%;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 0.75rem;
            padding: 0.875rem 1rem;
            color: #ffffff;
            font-size: 0.925rem;
            transition: all 0.25s ease;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
        }

        .clean-input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .clean-input:focus {
            outline: none;
            background: rgba(255, 255, 255, 0.2);
            border-color: rgba(239, 68, 68, 0.5);
            box-shadow: 0 0 0 4px rgba(239, 68, 68, 0.15);
        }

        /* Apple-inspired solid button */
        .action-button {
            width: 100%;
            background: #ffffff;
            color: #09090b;
            padding: 0.95rem;
            border-radius: 0.75rem;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.25s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .action-button:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
        }

        select.clean-input option {
            background-color: #120c11;
            color: #ffffff;
        }

        /* Screen responsiveness */
        @media (max-width: 900px) {
            .art-side {
                display: none; 
            }
            .form-side {
                width: 100%;
                background: rgba(15, 10, 15, 0.75);
                backdrop-filter: blur(25px);
                -webkit-backdrop-filter: blur(25px);
                padding: 3rem 2rem;
            }
            .master-card {
                max-height: 640px;
                max-width: 480px;
            }
        }
    </style>
</head>
<body>

    <!-- 3D SPLINE BACKGROUND -->
    <div id="canvas-container">
        <spline-viewer url="https://prod.spline.design/5lhVrjClMBbInbTG/scene.splinecode" loading="eager"></spline-viewer>
    </div>

    <!-- UI Overlay Containing the Unified Split Card -->
    <div id="ui-container">
        <div class="master-card">
            
            <!-- LEFT PANEL: Elegant Typography Overlay on Spline -->
            <div class="art-side select-none">
                <div class="flex items-center gap-4">
                    <span class="text-xs font-semibold uppercase tracking-[0.25em] text-white/70">STOCKFLOW SYSTEMS</span>
                    <div class="h-[1px] w-20 bg-white/20"></div>
                </div>

                <div class="my-auto">
                    <h2 class="serif-title text-6xl lg:text-[4.25rem] leading-[1.1] font-normal text-white mb-6">
                        Get<br>Everything<br>You Want
                    </h2>
                    <p class="text-sm text-gray-300 font-light leading-relaxed max-w-sm">
                        Monitor operations, track real-time inventory trajectories, and navigate market flow logistics with absolute precision.
                    </p>
                </div>

                <div class="text-[10px] text-gray-400 font-mono tracking-widest uppercase">
                    SECURE INTERACTION CODES // V4.0
                </div>
            </div>

            <!-- RIGHT PANEL: Interactive Dashboard Forms with Apple-style Frosted Glass -->
            <div class="form-side">

                <!-- PHP Error Alerts -->
                <?php if(isset($_SESSION['login_error'])): ?>
                    <div class="bg-red-500/20 text-red-100 border border-red-500/30 text-xs p-3 rounded-lg mb-4 text-center font-medium">
                        <?php echo $_SESSION['login_error']; unset($_SESSION['login_error']); ?>
                    </div>
                <?php endif; ?>

                <?php if(isset($_SESSION['register_error'])): ?>
                    <div class="bg-red-500/20 text-red-100 border border-red-500/30 text-xs p-3 rounded-lg mb-4 text-center font-medium">
                        <?php echo $_SESSION['register_error']; unset($_SESSION['register_error']); ?>
                    </div>
                <?php endif; ?>

                <!-- Animated Form Wrapper -->
                <div class="form-container-wrapper">
                    
                    <!-- LOGIN FORM -->
                    <form id="loginForm" method="POST" action="login_register.php" class="sliding-form space-y-6">
                        <div class="text-center mb-2">
                            <h3 class="serif-title text-4xl font-normal mb-2">Welcome Back</h3>
                            <p class="text-sm">Enter your credentials to access your account</p>
                        </div>

                        <!-- Email Input -->
                        <div>
                            <label class="block text-xs font-medium form-label uppercase tracking-wider mb-2">Email</label>
                            <input type="email" name="email" required class="clean-input" placeholder="Enter your email">
                        </div>

                        <!-- Password Input -->
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <label class="block text-xs font-medium form-label uppercase tracking-wider">Access Code</label>
                            </div>
                            <input type="password" name="password" required class="clean-input" placeholder="Enter your access code">
                        </div>

                        <!-- Remember Row (Forgot Password link has been removed completely) -->
                        <div class="flex items-center justify-between text-xs text-gray-200">
                            <label class="flex items-center gap-2 cursor-pointer select-none">
                                <input type="checkbox" class="rounded border-gray-400 bg-transparent text-red-600 focus:ring-red-500 h-4 w-4">
                                <span>Remember me</span>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" name="login" class="action-button mt-2">
                            Sign In
                        </button>
                    </form>

                    <!-- REGISTER FORM -->
                    <form id="registerForm" method="POST" action="login_register.php" class="sliding-form space-y-4">
                        <div class="text-center mb-2">
                            <h3 class="serif-title text-4xl font-normal mb-2">Create Account</h3>
                            <p class="text-sm">Register coordinate clearances for security node</p>
                        </div>

                        <!-- Name -->
                        <div>
                            <label class="block text-xs font-medium form-label uppercase tracking-wider mb-1.5">Full Name</label>
                            <input type="text" name="name" required class="clean-input" placeholder="Your Name">
                        </div>

                        <!-- Email -->
                        <div>
                            <label class="block text-xs font-medium form-label uppercase tracking-wider mb-1.5">Email</label>
                            <input type="email" name="email" required class="clean-input" placeholder="user@stockflow.com">
                        </div>

                        <!-- Password -->
                        <div>
                            <label class="block text-xs font-medium form-label uppercase tracking-wider mb-1.5">Password</label>
                            <input type="password" name="password" required class="clean-input" placeholder="••••••••">
                        </div>

                        <!-- Role Selection -->
                        <div>
                            <label class="block text-xs font-medium form-label uppercase tracking-wider mb-1.5">Authorization Clearance</label>
                            <select name="role" required class="clean-input appearance-none cursor-pointer">
                                <option value="user">Standard User</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" name="register" class="action-button mt-2">
                            Establish Clearance
                        </button>
                    </form>

                </div>

                <!-- Footer Toggling Links -->
                <div class="text-center mt-6 text-sm text-gray-200">
                    <span id="toggleTextPrefix">Don't have an account?</span>
                    <a href="#" id="toggleFormBtn" class="font-semibold hover:text-red-400 transition-colors ml-1">Sign Up</a>
                </div>

            </div>
        </div>
    </div>

    <!-- UI INTERACTION AND SPLINE KEEP-ALIVE SYSTEM SCRIPTS -->
    <script>
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const toggleFormBtn = document.getElementById('toggleFormBtn');
        const toggleTextPrefix = document.getElementById('toggleTextPrefix');

        let isRegisterActive = <?php echo ($active_form == 'register') ? 'true' : 'false'; ?>;

        function updateToggleState() {
            if (isRegisterActive) {
                loginForm.className = "sliding-form space-y-6 form-hidden-left";
                registerForm.className = "sliding-form space-y-4 form-visible";
                
                toggleTextPrefix.textContent = "Already have an account?";
                toggleFormBtn.textContent = "Sign In";
            } else {
                loginForm.className = "sliding-form space-y-6 form-visible";
                registerForm.className = "sliding-form space-y-4 form-hidden-right";
                
                toggleTextPrefix.textContent = "Don't have an account?";
                toggleFormBtn.textContent = "Sign Up";
            }
        }

        toggleFormBtn.addEventListener('click', (e) => {
            e.preventDefault();
            
            if (isRegisterActive) {
                registerForm.className = "sliding-form space-y-4 form-hidden-right";
            } else {
                loginForm.className = "sliding-form space-y-6 form-hidden-left";
            }
            
            setTimeout(() => {
                isRegisterActive = !isRegisterActive;
                updateToggleState();
            }, 60);
        });

        updateToggleState();

        const viewer = document.querySelector('spline-viewer');
        if (viewer) {
            viewer.addEventListener('load', () => {
                const canvas = viewer.shadowRoot?.querySelector('#canvas3d');
                if (canvas) {
                    let moveToggle = false;
                    setInterval(() => {
                        // Creating a small alternating pixel shift to force redraw parameters
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