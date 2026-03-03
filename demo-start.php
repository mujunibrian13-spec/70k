<?php
/**
 * Demo Start Page
 * Quick access to demo features
 */
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>70K Savings & Loans - Demo Start</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        
        .container {
            max-width: 1000px;
        }
        
        .header {
            text-align: center;
            color: white;
            margin-bottom: 50px;
        }
        
        .header h1 {
            font-size: 3.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 40px;
        }
        
        .feature-card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            transition: transform 0.3s, box-shadow 0.3s;
            text-decoration: none;
            color: inherit;
            display: flex;
            flex-direction: column;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            text-decoration: none;
            color: inherit;
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 20px;
        }
        
        .feature-title {
            font-size: 1.3rem;
            font-weight: bold;
            margin-bottom: 10px;
            color: #333;
        }
        
        .feature-description {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 20px;
            flex-grow: 1;
        }
        
        .feature-button {
            display: inline-block;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border-radius: 5px;
            font-weight: bold;
            text-decoration: none;
            text-align: center;
            transition: background 0.3s;
        }
        
        .feature-button:hover {
            background: #764ba2;
            text-decoration: none;
            color: white;
        }
        
        .credentials-section {
            background: white;
            border-radius: 10px;
            padding: 40px;
            margin-bottom: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .credentials-title {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 30px;
            color: #667eea;
        }
        
        .credential-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .credential-label {
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }
        
        .credential-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .credential-item:last-child {
            border-bottom: none;
        }
        
        .credential-value {
            font-family: monospace;
            color: #0066cc;
            font-weight: bold;
        }
        
        .color-admin {
            border-left-color: #dc3545;
        }
        
        .color-member {
            border-left-color: #28a745;
        }
        
        .quick-links {
            background: white;
            border-radius: 10px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        .quick-links h3 {
            color: #667eea;
            margin-bottom: 20px;
            font-weight: bold;
        }
        
        .link-list {
            list-style: none;
            padding: 0;
            columns: 2;
        }
        
        .link-list li {
            margin-bottom: 15px;
            break-inside: avoid;
        }
        
        .link-list a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .link-list a:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .badge-new {
            background: #ff6b6b;
            color: white;
            padding: 4px 8px;
            border-radius: 3px;
            font-size: 0.75rem;
            margin-left: 5px;
        }
        
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2.5rem;
            }
            
            .link-list {
                columns: 1;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1><i class="fas fa-piggy-bank"></i> 70K Savings & Loans</h1>
            <p>Demo Start - Quick Setup & Navigation</p>
        </div>
        
        <!-- Feature Grid -->
        <div class="feature-grid">
            <!-- Fix Admin Login (If Having Issues) -->
            <a href="fix_admin_login.php" class="feature-card">
                <div class="feature-icon" style="color: #ff6b6b;">
                    <i class="fas fa-lock"></i>
                </div>
                <div class="feature-title">Fix Admin Login</div>
                <div class="feature-description">
                    If you get a redirect loop when logging in as admin, click here to fix it automatically.
                </div>
                <div class="feature-button">Fix Login Issue</div>
            </a>
            
            <!-- Setup Undo Tables (Required First!) -->
            <a href="setup_undo_tables.php" class="feature-card">
                <div class="feature-icon" style="color: #667eea;">
                    <i class="fas fa-database"></i>
                </div>
                <div class="feature-title">Setup Database</div>
                <div class="feature-description">
                    Create the required database tables for the Delete & Restore feature.
                </div>
                <div class="feature-button">Setup Database</div>
            </a>
            
            <!-- Create Demo Member -->
            <a href="setup_demo_member.php" class="feature-card">
                <div class="feature-icon" style="color: #28a745;">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div class="feature-title">Create Demo Member</div>
                <div class="feature-description">
                    Automatically create a test member with sample data. No manual setup required.
                </div>
                <div class="feature-button">Create Demo</div>
            </a>
            
            <!-- Member Login -->
            <a href="login.php" class="feature-card">
                <div class="feature-icon" style="color: #28a745;">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
                <div class="feature-title">Member Login</div>
                <div class="feature-description">
                    Login as a member to view savings, apply for loans, and manage your account.
                </div>
                <div class="feature-button">Login</div>
            </a>
            
            <!-- Admin Dashboard -->
            <a href="admin.php" class="feature-card">
                <div class="feature-icon" style="color: #dc3545;">
                    <i class="fas fa-shield-alt"></i>
                </div>
                <div class="feature-title">Admin Dashboard</div>
                <div class="feature-description">
                    View system statistics, manage members, approve loans and payments.
                </div>
                <div class="feature-button">Go to Admin</div>
            </a>
            
            <!-- Documentation -->
            <a href="GETTING_STARTED.md" target="_blank" class="feature-card">
                <div class="feature-icon" style="color: #0066cc;">
                    <i class="fas fa-book"></i>
                </div>
                <div class="feature-title">Getting Started Guide</div>
                <div class="feature-description">
                    Step-by-step walkthrough of all features and demo workflows.
                </div>
                <div class="feature-button">Read Guide</div>
            </a>
            
            <!-- Demo Checklist -->
            <a href="DEMO_CHECKLIST.md" target="_blank" class="feature-card">
                <div class="feature-icon" style="color: #ffc107;">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="feature-title">Demo Checklist</div>
                <div class="feature-description">
                    Complete demo walkthrough with timing and key points to highlight.
                </div>
                <div class="feature-button">View Checklist</div>
            </a>
            
            <!-- System Status -->
            <div class="feature-card" style="background: #f0f7ff; border: 2px solid #667eea;">
                <div class="feature-icon" style="color: #667eea;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="feature-title">System Ready</div>
                <div class="feature-description">
                    All features are installed and ready. Your system is production-ready!
                </div>
                <div style="color: #28a745; font-weight: bold;">✓ All Systems Go</div>
            </div>
        </div>
        
        <!-- Credentials Section -->
        <div class="credentials-section">
            <div class="credentials-title">
                <i class="fas fa-key"></i> Demo Credentials
            </div>
            
            <div class="row">
                <div class="col-md-6">
                    <div class="credential-box color-admin">
                        <div class="credential-label">
                            <i class="fas fa-shield-alt"></i> Admin Account
                        </div>
                        <div class="credential-item">
                            <span>Email:</span>
                            <span class="credential-value">admin@70k.local</span>
                        </div>
                        <div class="credential-item">
                            <span>Password:</span>
                            <span class="credential-value">admin123</span>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="credential-box color-member">
                        <div class="credential-label">
                            <i class="fas fa-user"></i> Demo Member Account
                        </div>
                        <div class="credential-item">
                            <span>Email:</span>
                            <span class="credential-value">demo@70k.local</span>
                        </div>
                        <div class="credential-item">
                            <span>Password:</span>
                            <span class="credential-value">demo123</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Links -->
        <div class="quick-links">
            <h3><i class="fas fa-link"></i> Quick Links & Documentation</h3>
            <ul class="link-list">
                <li>
                    <a href="GETTING_STARTED.md" target="_blank">
                        <i class="fas fa-play-circle"></i> Getting Started
                    </a>
                </li>
                <li>
                    <a href="README.md" target="_blank">
                        <i class="fas fa-file-alt"></i> System README
                    </a>
                </li>
                <li>
                    <a href="SETUP.md" target="_blank">
                        <i class="fas fa-cog"></i> Installation Guide
                    </a>
                </li>
                <li>
                    <a href="PROJECT_STRUCTURE.md" target="_blank">
                        <i class="fas fa-sitemap"></i> Project Structure
                    </a>
                </li>
                <li>
                    <a href="UNDO_DELETE_MEMBER_FEATURE.md" target="_blank">
                        <i class="fas fa-undo"></i> Delete & Restore Feature
                        <span class="badge-new">NEW</span>
                    </a>
                </li>
                <li>
                    <a href="README_PAYMENT_APPROVAL.md" target="_blank">
                        <i class="fas fa-check-double"></i> Payment Approval
                    </a>
                </li>
                <li>
                    <a href="DEMO_CHECKLIST.md" target="_blank">
                        <i class="fas fa-list-check"></i> Demo Checklist
                    </a>
                </li>
                <li>
                    <a href="DEMO_MEMBER_SETUP.md" target="_blank">
                        <i class="fas fa-user-tie"></i> Demo Member Setup
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
