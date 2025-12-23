<!doctype html>
<html lang="en">
 <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Qwaiting - Queue Management Display</title>
  <style>
    body {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background: #f0f4f8;
      height: 100%;
      overflow: hidden;
    }

    html {
      height: 100%;
    }

    .main-container {
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: column;
      background: #ffffff;
      overflow: hidden;
    }

    /* Top Section */
    .top-section {
      flex: 1;
      min-height: 0;
      display: flex;
      overflow: hidden;
    }

    /* Video Display Area */
    .video-area {
      flex: 0 0 60%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 30px;
      position: relative;
      overflow: hidden;
    }

    .video-content {
      text-align: center;
      color: #ffffff;
      z-index: 1;
    }

    .qwaiting-logo {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 140px;
      height: 140px;
      background: rgba(255,255,255,0.15);
      backdrop-filter: blur(10px);
      border-radius: 50%;
      margin-bottom: 25px;
      border: 4px solid rgba(255,255,255,0.3);
      box-shadow: 0 8px 32px rgba(0,0,0,0.2);
    }

    .qwaiting-logo-text {
      font-size: 72px;
      font-weight: 900;
      color: #ffffff;
      text-shadow: 3px 3px 10px rgba(0,0,0,0.3);
      font-family: 'Segoe UI', Arial, sans-serif;
    }

    .video-content h2 {
      font-size: 52px;
      font-weight: 700;
      margin: 0 0 15px 0;
      text-shadow: 2px 2px 8px rgba(0,0,0,0.2);
    }

    .video-content p {
      font-size: 26px;
      margin: 0;
      opacity: 0.9;
    }

    .animated-bg {
      position: absolute;
      width: 100%;
      height: 100%;
      top: 0;
      left: 0;
      opacity: 0.1;
    }

    .animated-bg div {
      position: absolute;
      background: #ffffff;
      border-radius: 50%;
      animation: float 20s infinite ease-in-out;
    }

    .animated-bg div:nth-child(1) {
      width: 300px;
      height: 300px;
      top: 10%;
      left: 10%;
      animation-delay: 0s;
    }

    .animated-bg div:nth-child(2) {
      width: 200px;
      height: 200px;
      top: 60%;
      left: 70%;
      animation-delay: 5s;
    }

    .animated-bg div:nth-child(3) {
      width: 150px;
      height: 150px;
      top: 30%;
      left: 80%;
      animation-delay: 10s;
    }

    @keyframes float {
      0%, 100% { transform: translateY(0) translateX(0); }
      50% { transform: translateY(-30px) translateX(30px); }
    }

    /* Waiting Queue Panel */
    .queue-panel {
      flex: 0 0 40%;
      display: flex;
      flex-direction: column;
      padding: 15px;
      background: #f8fafc;
      border-left: 4px solid #e2e8f0;
      overflow: hidden;
    }

    .queue-title {
      font-size: 28px;
      font-weight: 700;
      color: #1e293b;
      margin: 0 0 10px 0;
      text-align: center;
      padding-bottom: 10px;
      border-bottom: 3px solid #3b82f6;
    }

    /* Queue Table */
    .queue-table {
      width: 100%;
      border-collapse: collapse;
    }

    .queue-table th {
      background: #3b82f6;
      color: #ffffff;
      padding: 10px;
      font-size: 20px;
      font-weight: 600;
      text-align: center;
      border: none;
    }

    .queue-table th:first-child {
      border-radius: 8px 0 0 0;
    }

    .queue-table th:last-child {
      border-radius: 0 8px 0 0;
    }

    .queue-table td {
      padding: 8px 10px;
      font-size: 18px;
      color: #334155;
      background: #ffffff;
      text-align: center;
      border-bottom: 1px solid #e2e8f0;
      font-weight: 600;
      line-height: 1.3;
    }

    .queue-table td:first-child {
      color: #3b82f6;
      font-weight: 700;
      font-size: 20px;
    }

    .queue-table tbody tr:hover {
      background: #eff6ff;
    }

    /* Missed and Hold Queue Section */
    .status-section {
      height: 70px;
      min-height: 70px;
      display: flex;
      flex-direction: column;
      background: #f8fafc;
      border-top: 2px solid #e2e8f0;
      flex-shrink: 0;
      padding: 0;
      gap: 1px;
    }

    .status-row {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 15px;
      padding: 0 40px;
      position: relative;
      border-left: 5px solid transparent;
    }

    .missed-row {
      background: #fff5f5;
      border-left-color: #991b1b;
    }

    .hold-row {
      background: #fffbeb;
      border-left-color: #92400e;
    }

    .status-row-label {
      font-size: 18px;
      font-weight: 700;
      min-width: 80px;
    }

    .missed-row .status-row-label {
      color: #991b1b;
    }

    .hold-row .status-row-label {
      color: #92400e;
    }

    .status-tokens {
      display: flex;
      gap: 12px;
      flex-wrap: wrap;
      align-items: center;
    }

    .token-badge {
      font-size: 18px;
      font-weight: 800;
      padding: 2px 10px;
      border-radius: 4px;
    }

    .missed-row .token-badge {
      color: #7f1d1d;
      background: #fecaca;
    }

    .hold-row .token-badge {
      color: #78350f;
      background: #fde68a;
    }

    /* Bottom Section - Counters */
    .counters-section {
      height: 220px;
      min-height: 220px;
      display: flex;
      background: #1e293b;
      border-top: 6px solid #3b82f6;
      flex-shrink: 0;
    }

    .counter-box {
      flex: 1;
      display: flex;
      flex-direction: column;
      border-right: 2px solid #334155;
    }

    .counter-box:last-child {
      border-right: none;
    }

    .counter-header {
      height: 45px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      font-weight: 700;
      color: #ffffff;
      background: #0f172a;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .counter-display {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background: #ffffff;
      margin: 10px;
      border-radius: 8px;
      box-shadow: inset 0 2px 8px rgba(0,0,0,0.1);
      padding: 8px;
      gap: 6px;
    }

    .current-token {
      font-size: 44px;
      font-weight: 900;
      color: #3b82f6;
      animation: pulse 2s ease-in-out infinite;
    }

    .previous-tokens {
      display: flex;
      flex-direction: column;
      gap: 3px;
      font-size: 16px;
      color: #64748b;
      font-weight: 600;
    }

    .counter-display.empty .current-token {
      color: #cbd5e1;
      animation: none;
    }

    @keyframes pulse {
      0%, 100% {
        transform: scale(1);
      }
      50% {
        transform: scale(1.05);
      }
    }

    /* Disclaimer Bar */
    .disclaimer-bar {
      height: 35px;
      min-height: 35px;
      background: #000000;
      display: flex;
      align-items: center;
      padding: 0;
      border-top: 2px solid #333333;
      flex-shrink: 0;
      overflow: hidden;
    }

    .disclaimer-text {
      font-size: 14px;
      font-weight: 700;
      color: #ffffff;
      line-height: 35px;
      white-space: nowrap;
    }

    /* Current Time Display */
    .time-display {
      position: absolute;
      top: 20px;
      right: 25px;
      background: rgba(255,255,255,0.2);
      backdrop-filter: blur(10px);
      padding: 12px 25px;
      border-radius: 12px;
      font-size: 24px;
      font-weight: 600;
      color: #ffffff;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
  </style>
  <style>@view-transition { navigation: auto; }</style>
  <script src="/_sdk/data_sdk.js" type="text/javascript"></script>
  <script src="/_sdk/element_sdk.js" type="text/javascript"></script>
  <script src="https://cdn.tailwindcss.com" type="text/javascript"></script>
 </head>
 <body>
  <main class="main-container"><!-- Top Section -->
   <div class="top-section"><!-- Video Display Area -->
    <div class="video-area">
     <div class="animated-bg">
      <div></div>
      <div></div>
      <div></div>
     </div>
     <div class="video-content">
      <div class="qwaiting-logo">
       <span class="qwaiting-logo-text">Q</span>
      </div>
      <h2>Welcome to Qwaiting</h2>
      <p>Your Queue Management Solution</p>
     </div>
     <div class="time-display" id="currentTime">
      10:45 AM
     </div>
    </div><!-- Waiting Queue Panel -->
    <div class="queue-panel">
     <h1 class="queue-title">Waiting Queue</h1>
     <table class="queue-table">
      <thead>
       <tr>
        <th>Token Num</th>
        <th>Waiting Time</th>
       </tr>
      </thead>
      <tbody>
       <tr>
        <td>A105</td>
        <td>3 mins</td>
       </tr>
       <tr>
        <td>A106</td>
        <td>6 mins</td>
       </tr>
       <tr>
        <td>A107</td>
        <td>9 mins</td>
       </tr>
       <tr>
        <td>A108</td>
        <td>12 mins</td>
       </tr>
       <tr>
        <td>A109</td>
        <td>15 mins</td>
       </tr>
       <tr>
        <td>A110</td>
        <td>18 mins</td>
       </tr>
       <tr>
        <td>A111</td>
        <td>21 mins</td>
       </tr>
       <tr>
        <td>A112</td>
        <td>24 mins</td>
       </tr>
       <tr>
        <td>A113</td>
        <td>27 mins</td>
       </tr>
       <tr>
        <td>A114</td>
        <td>30 mins</td>
       </tr>
      </tbody>
     </table>
    </div>
   </div><!-- Missed and Hold Queue Section -->
   <div class="status-section">
    <div class="status-row missed-row">
     <div class="status-row-label">
      Missed:
     </div>
     <div class="status-tokens">
      <span class="token-badge">A085</span>
      <span class="token-badge">A086</span>
      <span class="token-badge">A087</span>
      <span class="token-badge">A088</span>
      <span class="token-badge">A089</span>
     </div>
    </div>
    <div class="status-row hold-row">
     <div class="status-row-label">
      Hold :
     </div>
     <div class="status-tokens">
      <span class="token-badge">A090</span>
      <span class="token-badge">A091</span>
      <span class="token-badge">A093</span>
      <span class="token-badge">A094</span>
      <span class="token-badge">A095</span>
      <span class="token-badge">A096</span>
      <span class="token-badge">A097</span>
      <span class="token-badge">A098</span>
      <span class="token-badge">A099</span>
      <span class="token-badge">A100</span>
      <span class="token-badge">A101</span>
      <span class="token-badge">A125</span>
     </div>
    </div>
   </div><!-- Bottom Section - Service Counters -->
   <div class="counters-section">
    <div class="counter-box">
     <div class="counter-header">
      Counter 1
     </div>
     <div class="counter-display">
      <div class="current-token">
       A102
      </div>
     </div>
    </div>
    <div class="counter-box">
     <div class="counter-header">
      Counter 2
     </div>
     <div class="counter-display">
      <div class="current-token">
       A103
      </div>
     </div>
    </div>
    <div class="counter-box">
     <div class="counter-header">
      Counter 3
     </div>
     <div class="counter-display">
      <div class="current-token">
       A104
      </div>
     </div>
    </div>
    <div class="counter-box">
     <div class="counter-header">
      Counter 4
     </div>
     <div class="counter-display">
      <div class="current-token">
       A092
      </div>
     </div>
    </div>
    <div class="counter-box">
     <div class="counter-header">
      Counter 5
     </div>
     <div class="counter-display">
      <div class="current-token">
       A126
      </div>
     </div>
    </div>
    <div class="counter-box">
     <div class="counter-header">
      Counter 6
     </div>
     <div class="counter-display">
      <div class="current-token">
       A127
      </div>
     </div>
    </div>
   </div><!-- Disclaimer Bar -->
   <div class="disclaimer-bar">
    <marquee class="disclaimer-text" behavior="scroll" direction="left" scrollamount="5">
     For assistance, please contact our support team | This system is monitored 24/7 | Emergency contact: +1 (555) 123-4567 | Please wait for your token number to be called | Thank you for your patience
    </marquee>
   </div>
  </main>
  <script>
    // Update current time
    function updateTime() {
      const now = new Date();
      const hours = now.getHours();
      const minutes = now.getMinutes();
      const ampm = hours >= 12 ? 'PM' : 'AM';
      const displayHours = hours % 12 || 12;
      const displayMinutes = minutes < 10 ? '0' + minutes : minutes;
      document.getElementById('currentTime').textContent = `${displayHours}:${displayMinutes} ${ampm}`;
    }

    updateTime();
    setInterval(updateTime, 1000);
  </script>
 <script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9b26364f13a145af',t:'MTc2NjQ3NTY0MC4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>