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
      overflow-y: auto;
    }

    .queue-title {
      font-size: 24px;
      font-weight: 700;
      color: #1e293b;
      margin: 0 0 10px 0;
      text-align: center;
      padding-bottom: 8px;
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
      padding: 8px 10px;
      font-size: 16px;
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
      padding: 5px 8px;
      font-size: 15px;
      color: #334155;
      background: #ffffff;
      text-align: center;
      border-bottom: 1px solid #e2e8f0;
      font-weight: 600;
      line-height: 1.2;
    }

    .queue-table td:first-child {
      color: #3b82f6;
      font-weight: 700;
      font-size: 16px;
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
      border-top: 3px solid #cbd5e1;
      flex-shrink: 0;
      padding: 8px 40px;
      gap: 6px;
      justify-content: center;
    }

    .status-row {
      display: flex;
      align-items: center;
      gap: 15px;
    }

    .missed-row {
      background: #fee2e2;
    }

    .hold-row {
      background: #fef3c7;
    }

    .status-row-label {
      font-size: 16px;
      font-weight: 600;
      color: #475569;
      min-width: 60px;
    }

    .status-tokens {
      display: flex;
      gap: 10px;
      flex-wrap: wrap;
    }

    .token-badge {
      font-size: 16px;
      font-weight: 800;
    }

    .missed-row .token-badge {
      color: #dc2626;
    }

    .hold-row .token-badge {
      color: #d97706;
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
       <tr>
        <td>A115</td>
        <td>33 mins</td>
       </tr>
       <tr>
        <td>A116</td>
        <td>36 mins</td>
       </tr>
       <tr>
        <td>A117</td>
        <td>39 mins</td>
       </tr>
       <tr>
        <td>A118</td>
        <td>42 mins</td>
       </tr>
       <tr>
        <td>A119</td>
        <td>45 mins</td>
       </tr>
       <tr>
        <td>A120</td>
        <td>48 mins</td>
       </tr>
       <tr>
        <td>A121</td>
        <td>51 mins</td>
       </tr>
       <tr>
        <td>A122</td>
        <td>54 mins</td>
       </tr>
       <tr>
        <td>A123</td>
        <td>57 mins</td>
       </tr>
       <tr>
        <td>A124</td>
        <td>60 mins</td>
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
    <div class="counter-box">
     <div class="counter-header">
      Counter 7
     </div>
     <div class="counter-display">
      <div class="current-token">
       A128
      </div>
     </div>
    </div>
    <div class="counter-box">
     <div class="counter-header">
      Counter 8
     </div>
     <div class="counter-display">
      <div class="current-token">
       A129
      </div>
     </div>
    </div>
    <div class="counter-box">
     <div class="counter-header">
      Counter 9
     </div>
     <div class="counter-display">
      <div class="current-token">
       A130
      </div>
     </div>
    </div>
    <div class="counter-box">
     <div class="counter-header">
      Counter 10
     </div>
     <div class="counter-display">
      <div class="current-token">
       A131
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
</html><!doctype html>
<html lang="en" class="h-screen overflow-hidden">
 <head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Queue Management Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      box-sizing: border-box;
    }
    
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
    
    * {
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
    }
    
    .timeline-item::before {
      content: '';
      position: absolute;
      left: 9px;
      top: 20px;
      bottom: -8px;
      width: 2px;
      background: linear-gradient(to bottom, #e0e7ff, #fae8ff);
    }
    
    .timeline-item:last-child::before {
      display: none;
    }
    
    .nav-item {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      position: relative;
    }
    
    .nav-item::before {
      content: '';
      position: absolute;
      left: 0;
      top: 50%;
      transform: translateY(-50%);
      width: 4px;
      height: 0;
      background: white;
      border-radius: 0 4px 4px 0;
      transition: height 0.3s ease;
    }
    
    .nav-item:hover {
      background: rgba(255, 255, 255, 0.08);
    }
    
    .nav-item.active {
      background: rgba(255, 255, 255, 0.12);
    }
    
    .nav-item.active::before {
      height: 32px;
    }
    
    input:focus, select:focus {
      outline: none;
      border-color: #8b5cf6;
      box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
    }
    
    .visitor-card {
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
      border-left: 4px solid transparent;
    }
    
    .visitor-card:hover {
      box-shadow: 0 8px 24px rgba(139, 92, 246, 0.15);
      transform: translateX(4px);
    }
    
    .visitor-card.active {
      border-left-color: #8b5cf6;
    }
    
    .glass-effect {
      background: rgba(255, 255, 255, 0.9);
      backdrop-filter: blur(20px);
    }
    
    .stat-badge {
      animation: pulse-subtle 3s ease-in-out infinite;
    }
    
    @keyframes pulse-subtle {
      0%, 100% { opacity: 1; }
      50% { opacity: 0.8; }
    }

    #activity-panel {
      transition: width 0.3s ease, min-width 0.3s ease;
      overflow: hidden;
    }

    #activity-panel.collapsed {
      width: 48px !important;
      min-width: 48px !important;
    }

    #toggle-activity-log svg {
      transition: transform 0.3s ease;
    }

    .tab-btn {
      transition: all 0.2s ease;
      white-space: nowrap;
    }

    .tab-btn:hover {
      background: rgba(139, 92, 246, 0.05);
    }

    .detail-row {
      padding-bottom: 12px;
      border-bottom: 1px solid #f3f4f6;
    }

    .detail-row:last-child {
      border-bottom: none;
      padding-bottom: 0;
    }
  </style>
 </head>
<body class="h-screen w-full overflow-hidden" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
  <div class="flex flex-col h-full w-full">
   <div class="flex flex-1 min-h-0 overflow-hidden" style="max-height: calc(100vh - 72px);"><!-- Left Sidebar -->
    <div class="w-20" style="background: linear-gradient(180deg, rgba(139, 92, 246, 0.95) 0%, rgba(109, 40, 217, 0.95) 100%); backdrop-filter: blur(10px);">
     <div class="flex flex-col items-center py-6 space-y-2 h-full">
      <div class="w-12 h-12 bg-white rounded-2xl flex items-center justify-center mb-6 shadow-lg">
       <svg class="w-7 h-7" style="color: #8b5cf6;" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
       </svg>
      </div><button class="nav-item w-14 h-14 rounded-2xl flex items-center justify-center text-white">
       <svg class="w-6 h-6" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
       </svg></button> <button class="nav-item active w-14 h-14 rounded-2xl flex items-center justify-center text-white">
       <svg class="w-6 h-6" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
       </svg></button> <button class="nav-item w-14 h-14 rounded-2xl flex items-center justify-center text-white">
       <svg class="w-6 h-6" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
       </svg></button> <button class="nav-item w-14 h-14 rounded-2xl flex items-center justify-center text-white">
       <svg class="w-6 h-6" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
       </svg></button> <button class="nav-item w-14 h-14 rounded-2xl flex items-center justify-center text-white">
       <svg class="w-6 h-6" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
       </svg></button>
      <div class="flex-1"></div><button class="nav-item w-14 h-14 rounded-2xl flex items-center justify-center text-white">
       <svg class="w-6 h-6" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /> <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
       </svg></button>
     </div>
    </div><!-- Left Panel - Queue List -->
    <div id="left-panel" class="w-80 glass-effect border-r transition-all duration-300" style="border-color: rgba(139, 92, 246, 0.1);">
     <div class="flex flex-col h-full">
      <div class="p-6 border-b" style="border-color: rgba(139, 92, 246, 0.1);">
       <h1 id="system-title" class="text-xl font-bold text-gray-900">Queue Management System</h1>
       <div class="mt-3"><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Active Station</label> <select id="desk-select" class="w-full px-4 py-2.5 border-2 border-purple-200 rounded-xl text-sm font-bold text-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all bg-white"> <option value="desk1">Desk 1</option> <option value="desk2">Desk 2</option> <option value="desk3" selected>Desk 3</option> <option value="desk4">Desk 4</option> <option value="desk5">Desk 5</option> </select>
       </div>
      </div>
      <div class="flex-1 overflow-auto p-6"><!-- Waiting Queue -->
       <div>
        <div class="mb-4">
         <div class="flex items-center justify-between mb-3">
          <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Waiting Queue</h2>
          <span class="px-2.5 py-1.5 bg-purple-100 text-purple-700 text-xs font-bold rounded-full">6 visitors</span>
         </div>
         <div class="space-y-2">
          <select id="queue-filter" class="w-full px-3 py-2.5 border-2 border-purple-200 rounded-xl text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all bg-white hover:border-purple-300">
           <option value="all">All Visitors</option>
           <option value="walk-in">Walk-in</option>
           <option value="appointment">Appointment</option>
          </select>
          <select id="service-filter" class="w-full px-3 py-2.5 border-2 border-purple-200 rounded-xl text-sm font-semibold text-gray-700 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all bg-white hover:border-purple-300">
           <option value="all">All Services</option>
           <option value="general">General Consultation</option>
           <option value="medical">Medical Check</option>
           <option value="lab">Lab Results</option>
           <option value="prescription">Prescription</option>
           <option value="followup">Follow-up</option>
          </select>
         </div>
        </div>
        <div class="space-y-3">
         <div class="visitor-card bg-white rounded-2xl p-4 cursor-pointer shadow-md">
          <div class="flex items-start justify-between mb-3">
           <div>
            <h3 class="font-bold text-gray-900 text-base">Michael Chen</h3>
            <div class="flex items-center gap-1.5 text-xs text-gray-500 mt-1">
             <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
             </svg><span class="font-semibold">12 min</span>
            </div>
           </div><span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs font-bold rounded-lg">Medical Check</span>
          </div>
          <div class="text-xs font-medium text-gray-400 uppercase tracking-wide">
           Not Assigned
          </div>
         </div>
         <div class="visitor-card bg-white rounded-2xl p-4 cursor-pointer shadow-md">
          <div class="flex items-start justify-between mb-3">
           <div>
            <h3 class="font-bold text-gray-900 text-base">Emma Williams</h3>
            <div class="flex items-center gap-1.5 text-xs text-gray-500 mt-1">
             <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
             </svg><span class="font-semibold">8 min</span>
            </div>
           </div><span class="px-3 py-1 bg-pink-100 text-pink-700 text-xs font-bold rounded-lg">Lab Results</span>
          </div>
          <div class="text-xs font-medium text-gray-400 uppercase tracking-wide">
           Not Assigned
          </div>
         </div>
         <div class="visitor-card bg-white rounded-2xl p-4 cursor-pointer shadow-md">
          <div class="flex items-start justify-between mb-3">
           <div>
            <h3 class="font-bold text-gray-900 text-base">James Rodriguez</h3>
            <div class="flex items-center gap-1.5 text-xs text-gray-500 mt-1">
             <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
             </svg><span class="font-semibold">5 min</span>
            </div>
           </div><span class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-lg">Prescription</span>
          </div>
          <div class="text-xs font-medium text-gray-400 uppercase tracking-wide">
           Not Assigned
          </div>
         </div>
        </div>
       </div>
      </div>
     </div>
    </div><!-- Middle Panel - Visitor Details -->
    <div id="middle-panel" class="flex-1 flex flex-col min-h-0 transition-all duration-300" style="background: #ffffff; max-height: calc(100vh - 72px);">
     <div class="glass-effect border-b p-5 flex-shrink-0" style="border-color: rgba(139, 92, 246, 0.1);">
      <div class="flex items-center justify-between">
       <div>
        <div class="flex items-center gap-3">
         <h1 id="detail-name" class="text-2xl font-bold text-gray-900">Sarah Johnson</h1>
         <span class="px-3 py-1.5 bg-purple-600 text-white text-lg font-bold rounded-lg">A025</span>
        </div>
        <div class="flex items-center gap-2 mt-2"><span class="px-3 py-1 text-white text-sm font-bold rounded-lg" style="background: linear-gradient(135deg, #10b981, #059669);">● Serving now</span> <span class="px-2.5 py-1 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg">1h 2m</span>
        </div>
       </div><button class="p-3 hover:bg-gray-100 rounded-xl transition-all">
        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
        </svg></button>
      </div>
     </div>
     <div class="flex-1 overflow-auto" style="min-height: 0; max-height: calc(100vh - 200px);">
      <div class="p-6">
       <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-lg p-6">
        <h2 class="text-lg font-bold text-gray-900 mb-5 flex items-center gap-2">
         <svg class="w-5 h-5" style="color: #8b5cf6;" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
         </svg> Visitor Information</h2>
        <form class="space-y-6">
         <!-- Personal Information Section -->
         <div class="space-y-4">
          <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide border-b pb-2">Personal Information</h3>
          <div class="grid grid-cols-2 gap-4">
           <div><label class="block text-sm font-bold text-gray-700 mb-2">First name</label> <input type="text" value="Sarah" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl text-sm text-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all font-medium">
           </div>
           <div><label class="block text-sm font-bold text-gray-700 mb-2">Last name</label> <input type="text" value="Johnson" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl text-sm text-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all font-medium">
           </div>
          </div>
         </div>
         
         <!-- Contact Information Section -->
         <div class="space-y-4">
          <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide border-b pb-2">Contact Information</h3>
          <div><label class="block text-sm font-bold text-gray-700 mb-2">Email address</label> <input type="email" value="sarah.johnson@email.com" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl text-sm text-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all font-medium">
          </div>
          <div><label class="block text-sm font-bold text-gray-700 mb-2">Phone number</label> <input type="tel" value="+1 (555) 123-4567" class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl text-sm text-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all font-medium">
          </div>
         </div>
         
         <!-- Service Details Section -->
         <div class="space-y-4">
          <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide border-b pb-2">Service Details</h3>
          
          <!-- Current Service Hierarchy Display -->
          <div class="p-4 bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl border-2 border-purple-200">
           <label class="block text-xs font-bold text-gray-600 mb-3 uppercase tracking-wide">Current Service Selection</label>
           <div class="space-y-2">
            <div class="flex items-center gap-2">
             <span class="w-6 h-6 rounded-full bg-purple-600 text-white flex items-center justify-center text-xs font-bold flex-shrink-0">1</span>
             <div>
              <p class="text-xs text-gray-600 font-semibold">Parent Service</p>
              <p class="text-sm font-bold text-gray-900">Laboratory Services</p>
             </div>
            </div>
            <div class="flex items-center gap-2 ml-3">
             <span class="w-6 h-6 rounded-full bg-purple-500 text-white flex items-center justify-center text-xs font-bold flex-shrink-0">2</span>
             <div>
              <p class="text-xs text-gray-600 font-semibold">Sub-Parent Service</p>
              <p class="text-sm font-bold text-gray-800">Blood Tests</p>
             </div>
            </div>
            <div class="flex items-center gap-2 ml-6">
             <span class="w-6 h-6 rounded-full bg-purple-400 text-white flex items-center justify-center text-xs font-bold flex-shrink-0">3</span>
             <div>
              <p class="text-xs text-gray-600 font-semibold">Child Service</p>
              <p class="text-sm font-bold text-gray-700">Complete Blood Count (CBC)</p>
             </div>
            </div>
           </div>
           <div class="mt-3 pt-3 border-t border-purple-200">
            <p class="text-xs font-bold text-purple-700">Full Path: Laboratory Services → Blood Tests → Complete Blood Count (CBC)</p>
           </div>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
           <div><label class="block text-sm font-bold text-gray-700 mb-2">Preferred Language</label> 
            <select class="w-full px-4 py-2.5 border-2 border-gray-200 rounded-xl text-sm text-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all bg-white font-medium">
             <option>English</option> <option>Spanish</option> <option>French</option> <option>German</option> <option>Chinese</option>
            </select>
           </div>
          </div>
          
          <!-- Multi-Level Service Selection -->
          <div class="space-y-3 p-4 bg-gray-50 rounded-xl">
           <div class="flex items-start justify-between mb-3">
            <label class="block text-sm font-bold text-gray-700">Service Selection (Multi-Level)</label>
            <button type="button" id="show-service-examples" class="text-xs font-semibold text-purple-600 hover:text-purple-700 underline">Show Examples</button>
           </div>
           
           <!-- Service Hierarchy Examples (Hidden by default) -->
           <div id="service-examples" class="hidden mb-3 p-3 bg-white border-2 border-purple-200 rounded-lg">
            <p class="text-xs font-bold text-gray-700 mb-2">📋 Service Hierarchy Examples:</p>
            <div class="space-y-2 text-xs">
             <div class="flex items-start gap-2">
              <span class="text-purple-600 font-bold">1.</span>
              <div>
               <span class="font-semibold text-gray-900">Medical Check</span>
               <span class="text-gray-500"> → </span>
               <span class="font-semibold text-gray-700">Physical Examination</span>
               <span class="text-gray-500"> → </span>
               <span class="text-gray-600">Routine Check-up</span>
              </div>
             </div>
             <div class="flex items-start gap-2">
              <span class="text-purple-600 font-bold">2.</span>
              <div>
               <span class="font-semibold text-gray-900">Laboratory Services</span>
               <span class="text-gray-500"> → </span>
               <span class="font-semibold text-gray-700">Blood Tests</span>
               <span class="text-gray-500"> → </span>
               <span class="text-gray-600">Complete Blood Count (CBC)</span>
              </div>
             </div>
             <div class="flex items-start gap-2">
              <span class="text-purple-600 font-bold">3.</span>
              <div>
               <span class="font-semibold text-gray-900">Prescription</span>
               <span class="text-gray-500"> → </span>
               <span class="font-semibold text-gray-700">New Prescription</span>
               <span class="text-gray-500"> → </span>
               <span class="text-gray-600">Chronic Condition</span>
              </div>
             </div>
            </div>
            <div class="mt-2 pt-2 border-t border-purple-100">
             <p class="text-xs text-gray-600"><span class="font-bold text-gray-900">Level 1:</span> Parent Service | <span class="font-bold text-gray-900">Level 2:</span> Sub-Parent Service | <span class="font-bold text-gray-900">Level 3:</span> Child Service</p>
            </div>
           </div>
           
           <!-- Level 1: Parent Service -->
           <div>
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
             <span class="inline-flex items-center gap-1">
              <span class="w-5 h-5 bg-purple-600 text-white rounded-full flex items-center justify-center text-xs font-bold">1</span>
              <span>Level 1 - Parent Service</span>
             </span>
            </label>
            <select id="service-level-1" class="w-full px-4 py-2.5 border-2 border-purple-300 rounded-xl text-sm text-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all bg-white font-medium">
             <option value="">-- Select Parent Service --</option>
             <option value="general">General Consultation</option>
             <option value="medical">Medical Check</option>
             <option value="lab">Laboratory Services</option>
             <option value="prescription">Prescription</option>
             <option value="followup">Follow-up</option>
            </select>
           </div>
           
           <!-- Level 2: Sub-Parent Service -->
           <div id="level-2-container" class="hidden">
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
             <span class="inline-flex items-center gap-1">
              <span class="w-5 h-5 bg-purple-500 text-white rounded-full flex items-center justify-center text-xs font-bold">2</span>
              <span>Level 2 - Sub-Parent Service</span>
             </span>
            </label>
            <select id="service-level-2" class="w-full px-4 py-2.5 border-2 border-purple-300 rounded-xl text-sm text-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all bg-white font-medium">
             <option value="">-- Select Sub-Parent Service --</option>
            </select>
           </div>
           
           <!-- Level 3: Child Service -->
           <div id="level-3-container" class="hidden">
            <label class="block text-xs font-semibold text-gray-600 mb-1.5">
             <span class="inline-flex items-center gap-1">
              <span class="w-5 h-5 bg-purple-400 text-white rounded-full flex items-center justify-center text-xs font-bold">3</span>
              <span>Level 3 - Child Service</span>
             </span>
            </label>
            <select id="service-level-3" class="w-full px-4 py-2.5 border-2 border-purple-300 rounded-xl text-sm text-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all bg-white font-medium">
             <option value="">-- Select Child Service --</option>
            </select>
           </div>
           
           <!-- Selected Service Display -->
           <div id="selected-service-display" class="hidden mt-3 p-3 bg-white border-2 border-purple-200 rounded-lg">
            <p class="text-xs font-semibold text-gray-600 mb-1">Selected Service:</p>
            <p id="selected-service-text" class="text-sm font-bold text-purple-700"></p>
           </div>
          </div>
         </div>
         
         <!-- Save Button -->
         <div class="pt-4 border-t">
          <button type="submit" id="save-visitor-btn" class="w-full bg-gradient-to-r from-purple-600 to-purple-700 hover:from-purple-700 hover:to-purple-800 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-lg hover:shadow-xl flex items-center justify-center gap-2">
           <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
           </svg>
           Save Visitor Information
          </button>
         </div>
        </form>
       </div>
      </div>
     </div>
     <div class="glass-effect border-t p-4 flex-shrink-0" style="border-color: rgba(139, 92, 246, 0.1);">
      <div class="max-w-3xl mx-auto">
       <div class="flex items-center gap-3"><button id="finish-btn" class="flex-1 text-white font-bold py-3 px-6 rounded-xl transition-all shadow-lg hover:shadow-xl text-sm" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);"> Complete Service </button> <button id="call-next-btn" class="flex-1 bg-white border-2 border-purple-200 hover:border-purple-300 text-gray-700 font-bold py-3 px-6 rounded-xl transition-all shadow-md hover:shadow-lg text-sm"> Next Visitor </button>
        <div class="relative"><button id="more-menu-btn" class="p-4 bg-white border-2 border-gray-200 hover:border-gray-300 rounded-xl transition-all shadow-md hover:shadow-lg">
          <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z" />
          </svg></button>
         <div id="more-menu" class="hidden absolute bottom-full right-0 mb-2 w-48 bg-white rounded-xl shadow-xl border-2 border-gray-100 overflow-hidden z-10"><button class="w-full px-4 py-3 text-left text-sm font-semibold text-gray-700 hover:bg-purple-50 transition-colors flex items-center gap-2">
           <svg class="w-4 h-4" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
           </svg> Hold </button> <button class="w-full px-4 py-3 text-left text-sm font-semibold text-gray-700 hover:bg-purple-50 transition-colors flex items-center gap-2">
           <svg class="w-4 h-4" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
           </svg> Skip </button> <button class="w-full px-4 py-3 text-left text-sm font-semibold text-gray-700 hover:bg-purple-50 transition-colors flex items-center gap-2">
           <svg class="w-4 h-4" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
           </svg> Transfer </button> <button class="w-full px-4 py-3 text-left text-sm font-semibold text-gray-700 hover:bg-purple-50 transition-colors flex items-center gap-2">
           <svg class="w-4 h-4" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
           </svg> Cancel </button> <button class="w-full px-4 py-3 text-left text-sm font-semibold text-gray-700 hover:bg-purple-50 transition-colors flex items-center gap-2">
           <svg class="w-4 h-4" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
           </svg> Recall </button> <button class="w-full px-4 py-3 text-left text-sm font-semibold text-gray-700 hover:bg-purple-50 transition-colors flex items-center gap-2">
           <svg class="w-4 h-4" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 15l-3-3m0 0l3-3m-3 3h8M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
           </svg> Move Back </button>
         </div>
        </div>
       </div>
      </div>
     </div>
    </div><!-- Right Panel - Visitor Details Tabs -->
    <div id="activity-panel" class="w-80 glass-effect border-l flex flex-col overflow-hidden transition-all duration-300" style="border-color: rgba(139, 92, 246, 0.1);">
     <!-- Header with Toggle -->
     <div class="p-4 border-b flex-shrink-0" style="border-color: rgba(139, 92, 246, 0.1);">
      <div class="flex items-center justify-between">
       <h2 class="text-base font-bold text-gray-900">Visitor Details</h2>
       <button id="toggle-activity-log" class="p-1.5 hover:bg-gray-100 rounded-lg transition-all" title="Toggle Panel">
        <svg class="w-4 h-4 text-gray-600 transition-transform duration-300" fill="none" stroke="currentColor" viewbox="0 0 24 24">
         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
       </button>
      </div>
     </div>
     
     <!-- Tabs Navigation -->
     <div class="border-b flex-shrink-0" style="border-color: rgba(139, 92, 246, 0.1);">
      <div class="flex">
       <button class="tab-btn active px-4 py-2.5 text-sm font-semibold border-b-2 border-purple-600 text-purple-600 transition-all" data-tab="details">Details</button>
       <button class="tab-btn px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-all" data-tab="notes">Notes</button>
       <button class="tab-btn px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-all" data-tab="messages">Messages</button>
       <button class="tab-btn px-4 py-2.5 text-sm font-semibold border-b-2 border-transparent text-gray-500 hover:text-gray-700 transition-all" data-tab="logs">Logs</button>
      </div>
     </div>
     
     <div id="activity-content" class="flex-1 overflow-auto">
      
      <!-- Details Tab -->
      <div id="tab-details" class="tab-content p-4">
       <div class="space-y-3">
        <div class="detail-row">
         <p class="text-xs font-semibold text-gray-500 mb-1">Token</p>
         <p class="text-sm font-bold text-gray-900">A025</p>
        </div>
        <div class="detail-row">
         <p class="text-xs font-semibold text-gray-500 mb-1">Name</p>
         <p class="text-sm font-bold text-gray-900">Sarah Johnson</p>
        </div>
        <div class="detail-row">
         <p class="text-xs font-semibold text-gray-500 mb-1">State</p>
         <span class="inline-block px-2 py-1 text-xs font-bold rounded-lg text-white" style="background: linear-gradient(135deg, #10b981, #059669);">● Serving now</span>
        </div>
        <div class="detail-row">
         <p class="text-xs font-semibold text-gray-500 mb-1">Phone</p>
         <p class="text-sm text-gray-900">+1 (555) 123-4567</p>
        </div>
        <div class="detail-row">
         <p class="text-xs font-semibold text-gray-500 mb-1">Email</p>
         <p class="text-sm text-gray-900">sarah.johnson@email.com</p>
        </div>
        <div class="detail-row">
         <p class="text-xs font-semibold text-gray-500 mb-2">Service Hierarchy</p>
         <div class="space-y-2 p-3 bg-gray-50 rounded-lg">
          <div class="flex items-start gap-2">
           <span class="w-5 h-5 rounded-full bg-purple-600 text-white flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">1</span>
           <div>
            <p class="text-[10px] text-gray-500 font-semibold uppercase">Parent</p>
            <p class="text-xs font-bold text-gray-900">Laboratory Services</p>
           </div>
          </div>
          <div class="flex items-start gap-2 ml-2">
           <span class="w-5 h-5 rounded-full bg-purple-500 text-white flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">2</span>
           <div>
            <p class="text-[10px] text-gray-500 font-semibold uppercase">Sub-Parent</p>
            <p class="text-xs font-bold text-gray-800">Blood Tests</p>
           </div>
          </div>
          <div class="flex items-start gap-2 ml-4">
           <span class="w-5 h-5 rounded-full bg-purple-400 text-white flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">3</span>
           <div>
            <p class="text-[10px] text-gray-500 font-semibold uppercase">Child</p>
            <p class="text-xs font-bold text-gray-700">Complete Blood Count (CBC)</p>
           </div>
          </div>
         </div>
        </div>
        <div class="detail-row">
         <p class="text-xs font-semibold text-gray-500 mb-1">Assigned to</p>
         <p class="text-sm text-gray-900">Emily Davis - Desk 3</p>
        </div>
        <div class="detail-row">
         <p class="text-xs font-semibold text-gray-500 mb-1">Created</p>
         <p class="text-sm text-gray-900">Aug 27, 9:21 AM</p>
        </div>
        <div class="detail-row">
         <p class="text-xs font-semibold text-gray-500 mb-1">Waited</p>
         <p class="text-sm text-gray-900">1h 2m</p>
        </div>
        <div class="detail-row">
         <p class="text-xs font-semibold text-gray-500 mb-1">Source</p>
         <p class="text-sm text-gray-900">Walk-in</p>
        </div>
        <div class="detail-row">
         <p class="text-xs font-semibold text-gray-500 mb-1">Date of Birth</p>
         <p class="text-sm text-gray-900">15 Mar 1985</p>
        </div>
        <div class="detail-row">
         <p class="text-xs font-semibold text-gray-500 mb-1">ID</p>
         <p class="text-xs text-gray-600 font-mono">zqJVBsE6IPrHFx70018o</p>
        </div>
       </div>
      </div>
      
      <!-- Notes Tab -->
      <div id="tab-notes" class="tab-content hidden p-4">
       <div class="space-y-3">
        <div class="bg-gray-50 rounded-lg p-3 border-l-4 border-blue-500">
         <p class="text-xs font-semibold text-gray-600 mb-1">Aug 27, 10:23 AM - Emily Davis</p>
         <p class="text-sm text-gray-800">Patient requested quick service. Regular visitor.</p>
        </div>
        <div class="bg-gray-50 rounded-lg p-3 border-l-4 border-blue-500">
         <p class="text-xs font-semibold text-gray-600 mb-1">Aug 27, 9:21 AM - System</p>
         <p class="text-sm text-gray-800">Check-in completed via kiosk.</p>
        </div>
       </div>
       <div class="mt-4">
        <textarea class="w-full px-3 py-2 border-2 border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500" rows="3" placeholder="Add a note..."></textarea>
        <button class="mt-2 w-full bg-purple-600 hover:bg-purple-700 text-white text-sm font-semibold py-2 px-4 rounded-lg transition-all">Add Note</button>
       </div>
      </div>
      
      <!-- Messages Tab -->
      <div id="tab-messages" class="tab-content hidden p-4">
       <div class="space-y-3">
        <div class="bg-blue-50 rounded-lg p-3 border-l-4 border-blue-500">
         <div class="flex items-center gap-2 mb-2">
          <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
          </svg>
          <p class="text-xs font-semibold text-blue-900">SMS - 10:23 AM</p>
         </div>
         <p class="text-sm text-gray-800">"Your turn! Please proceed to Desk 3 now."</p>
         <p class="text-xs text-gray-500 mt-1">Sent to: +1 (555) 123-4567</p>
        </div>
        <div class="bg-green-50 rounded-lg p-3 border-l-4 border-green-500">
         <div class="flex items-center gap-2 mb-2">
          <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
          </svg>
          <p class="text-xs font-semibold text-green-900">SMS - 9:21 AM</p>
         </div>
         <p class="text-sm text-gray-800">"Queue A025 confirmed. You are 8th in line. Estimated wait: 35 min."</p>
         <p class="text-xs text-gray-500 mt-1">Sent to: +1 (555) 123-4567</p>
        </div>
       </div>
      </div>
      
      <!-- Logs Tab -->
      <div id="tab-logs" class="tab-content hidden p-4">
      <div class="space-y-4">
       <div class="timeline-item relative pl-8">
        <div class="absolute left-0 top-0.5 w-5 h-5 rounded-full border-3 border-white shadow" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);"></div>
        <div class="text-xs">
         <div class="flex items-center gap-2 mb-1">
          <p class="font-bold text-gray-900">Called by Clerk</p>
          <span class="px-1.5 py-0.5 bg-purple-600 text-white text-xs font-bold rounded">A025</span>
         </div>
         <p class="text-gray-600 mt-0.5 text-xs">Emily Davis initiated service</p>
         <p class="text-gray-400 text-xs mt-1 font-semibold">10:23 AM</p>
        </div>
       </div>
       <div class="timeline-item relative pl-8">
        <div class="absolute left-0 top-0.5 w-5 h-5 rounded-full border-3 border-white shadow" style="background: linear-gradient(135deg, #06b6d4, #0891b2);"></div>
        <div class="text-xs">
         <div class="flex items-center gap-2 mb-1">
          <p class="font-bold text-gray-900">SMS Sent - Called</p>
          <span class="px-1.5 py-0.5 bg-purple-600 text-white text-xs font-bold rounded">A025</span>
         </div>
         <p class="text-gray-600 mt-0.5">Notification sent to +1 (555) 123-4567</p>
         <p class="text-gray-500 text-xs mt-1 bg-gray-50 rounded-lg p-1.5 font-medium">"Your turn! Please proceed to Desk 3 now."</p>
         <p class="text-gray-400 text-xs mt-1 font-semibold">10:23 AM</p>
        </div>
       </div>
       <div class="timeline-item relative pl-8">
        <div class="absolute left-0 top-0.5 w-5 h-5 rounded-full border-3 border-white shadow" style="background: linear-gradient(135deg, #3b82f6, #2563eb);"></div>
        <div class="text-xs">
         <div class="flex items-center gap-2 mb-1">
          <p class="font-bold text-gray-900">Desk Assignment</p>
          <span class="px-1.5 py-0.5 bg-purple-600 text-white text-xs font-bold rounded">A025</span>
         </div>
         <p class="text-gray-600 mt-0.5">Auto-assigned to Desk 3</p>
         <p class="text-gray-400 text-xs mt-1 font-semibold">10:22 AM</p>
        </div>
       </div>
       <div class="timeline-item relative pl-8">
        <div class="absolute left-0 top-0.5 w-5 h-5 rounded-full border-3 border-white shadow" style="background: linear-gradient(135deg, #10b981, #059669);"></div>
        <div class="text-xs">
         <div class="flex items-center gap-2 mb-1">
          <p class="font-bold text-gray-900">Check-in Complete</p>
          <span class="px-1.5 py-0.5 bg-purple-600 text-white text-xs font-bold rounded">A025</span>
         </div>
         <p class="text-gray-600 mt-0.5">Self-service kiosk registration</p>
         <p class="text-gray-400 text-xs mt-1 font-semibold">9:21 AM</p>
        </div>
       </div>
       <div class="timeline-item relative pl-8">
        <div class="absolute left-0 top-0.5 w-5 h-5 rounded-full border-3 border-white shadow" style="background: linear-gradient(135deg, #06b6d4, #0891b2);"></div>
        <div class="text-xs">
         <div class="flex items-center gap-2 mb-1">
          <p class="font-bold text-gray-900">SMS Sent - Queue Created</p>
          <span class="px-1.5 py-0.5 bg-purple-600 text-white text-xs font-bold rounded">A025</span>
         </div>
         <p class="text-gray-600 mt-0.5">Confirmation sent to +1 (555) 123-4567</p>
         <p class="text-gray-500 text-xs mt-1 bg-gray-50 rounded-lg p-1.5 font-medium">"Queue A025 confirmed. You are 8th in line. Estimated wait: 35 min."</p>
         <p class="text-gray-400 text-xs mt-1 font-semibold">9:21 AM</p>
        </div>
       </div>
       <div class="timeline-item relative pl-8">
        <div class="absolute left-0 top-0.5 w-5 h-5 rounded-full border-3 border-white shadow" style="background: linear-gradient(135deg, #ec4899, #db2777);"></div>
        <div class="text-xs">
         <p class="font-bold text-gray-900">Appointment Booked</p>
         <p class="text-gray-600 mt-0.5">Online scheduling system</p>
         <p class="text-gray-400 text-xs mt-1 font-semibold">Yesterday, 3:45 PM</p>
        </div>
       </div>
      </div>
     </div>
    </div>
   </div>
  </div>
  <!-- Bottom Status Bar -->
  <div class="border-t flex-shrink-0" style="border-color: rgba(139, 92, 246, 0.2); background: rgba(255, 255, 255, 0.9); backdrop-filter: blur(10px); min-height: 72px; max-height: 72px;">
   <div class="flex h-full"><!-- Extended Sidebar Space -->
    <div class="w-20" style="background: linear-gradient(180deg, rgba(139, 92, 246, 0.95) 0%, rgba(109, 40, 217, 0.95) 100%);"></div><!-- Stats Content -->
    <div class="flex-1 px-6 flex items-center">
     <div class="flex items-center justify-center gap-8 max-w-4xl mx-auto"><!-- Served Queues -->
      <div id="served-card" class="flex items-center gap-3 cursor-pointer hover:scale-105 transition-all group">
       <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #10b981, #059669);">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
       </div>
       <div>
        <p class="text-xs font-semibold text-gray-500 uppercase">Served</p>
        <p class="text-xl font-bold text-gray-900">24</p>
       </div>
      </div>
      <div class="w-px h-10 bg-gray-300"></div><!-- Missed Queues -->
      <div id="missed-card" class="flex items-center gap-3 cursor-pointer hover:scale-105 transition-all group">
       <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
       </div>
       <div>
        <p class="text-xs font-semibold text-gray-500 uppercase">Missed</p>
        <p class="text-xl font-bold text-gray-900">3</p>
       </div>
      </div>
      <div class="w-px h-10 bg-gray-300"></div><!-- Hold Queues -->
      <div id="hold-card" class="flex items-center gap-3 cursor-pointer hover:scale-105 transition-all group">
       <div class="w-10 h-10 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6m7-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
       </div>
       <div>
        <p class="text-xs font-semibold text-gray-500 uppercase">On Hold</p>
        <p class="text-xl font-bold text-gray-900">2</p>
       </div>
      </div>
     </div>
    </div>
   </div>
  </div><!-- Modal Overlay -->
  <div id="queue-modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50" style="backdrop-filter: blur(4px);">
   <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl mx-4 max-h-[80%] flex flex-col"><!-- Modal Header -->
    <div class="flex items-center justify-between p-6 border-b border-gray-200">
     <div>
      <h2 id="modal-title" class="text-2xl font-bold text-gray-900">Served Queues</h2>
      <p id="modal-subtitle" class="text-sm text-gray-500 mt-1">24 visitors completed today</p>
     </div><button id="close-modal" class="p-2 hover:bg-gray-100 rounded-xl transition-all">
      <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
      </svg></button>
    </div><!-- Modal Body -->
    <div class="flex-1 overflow-auto p-6">
     <div id="modal-content" class="space-y-3"><!-- Queue items will be inserted here -->
     </div>
    </div>
   </div>
  </div>
  <script>
    // Multi-level Service Structure
    const serviceHierarchy = {
      'general': {
        name: 'General Consultation',
        children: null
      },
      'medical': {
        name: 'Medical Check',
        children: {
          'physical': { 
            name: 'Physical Examination',
            children: {
              'routine': { name: 'Routine Check-up' },
              'comprehensive': { name: 'Comprehensive Exam' },
              'sports': { name: 'Sports Physical' }
            }
          },
          'diagnostic': { 
            name: 'Diagnostic Tests',
            children: {
              'xray': { name: 'X-Ray' },
              'ultrasound': { name: 'Ultrasound' },
              'ct': { name: 'CT Scan' }
            }
          }
        }
      },
      'lab': {
        name: 'Laboratory Services',
        children: {
          'blood': { 
            name: 'Blood Tests',
            children: {
              'cbc': { name: 'Complete Blood Count (CBC)' },
              'lipid': { name: 'Lipid Panel' },
              'glucose': { name: 'Glucose Test' },
              'thyroid': { name: 'Thyroid Function' }
            }
          },
          'urine': { 
            name: 'Urine Tests',
            children: {
              'basic': { name: 'Basic Urinalysis' },
              'complete': { name: 'Complete Urinalysis' },
              'culture': { name: 'Urine Culture' }
            }
          },
          'culture': { 
            name: 'Culture Tests',
            children: {
              'throat': { name: 'Throat Culture' },
              'wound': { name: 'Wound Culture' },
              'blood': { name: 'Blood Culture' }
            }
          }
        }
      },
      'prescription': {
        name: 'Prescription',
        children: {
          'new': { 
            name: 'New Prescription',
            children: {
              'acute': { name: 'Acute Condition' },
              'chronic': { name: 'Chronic Condition' }
            }
          },
          'refill': { 
            name: 'Prescription Refill',
            children: {
              'regular': { name: 'Regular Medication' },
              'controlled': { name: 'Controlled Substance' }
            }
          }
        }
      },
      'followup': {
        name: 'Follow-up',
        children: null
      }
    };

    // Handle cascading service selection
    const level1Select = document.getElementById('service-level-1');
    const level2Select = document.getElementById('service-level-2');
    const level3Select = document.getElementById('service-level-3');
    const level2Container = document.getElementById('level-2-container');
    const level3Container = document.getElementById('level-3-container');
    const selectedServiceDisplay = document.getElementById('selected-service-display');
    const selectedServiceText = document.getElementById('selected-service-text');

    // Level 1 change
    level1Select.addEventListener('change', function() {
      const selectedValue = this.value;
      
      // Reset level 2 and 3
      level2Select.innerHTML = '<option value="">-- Select Sub-Parent Service --</option>';
      level3Select.innerHTML = '<option value="">-- Select Child Service --</option>';
      level2Container.classList.add('hidden');
      level3Container.classList.add('hidden');
      selectedServiceDisplay.classList.add('hidden');
      
      if (selectedValue && serviceHierarchy[selectedValue].children) {
        // Populate level 2
        const children = serviceHierarchy[selectedValue].children;
        for (const [key, value] of Object.entries(children)) {
          const option = document.createElement('option');
          option.value = key;
          option.textContent = value.name;
          level2Select.appendChild(option);
        }
        level2Container.classList.remove('hidden');
      } else if (selectedValue) {
        // No children, show final selection
        selectedServiceText.textContent = serviceHierarchy[selectedValue].name;
        selectedServiceDisplay.classList.remove('hidden');
      }
    });

    // Level 2 change
    level2Select.addEventListener('change', function() {
      const level1Value = level1Select.value;
      const level2Value = this.value;
      
      // Reset level 3
      level3Select.innerHTML = '<option value="">-- Select Child Service --</option>';
      level3Container.classList.add('hidden');
      selectedServiceDisplay.classList.add('hidden');
      
      if (level2Value && serviceHierarchy[level1Value].children[level2Value].children) {
        // Populate level 3
        const children = serviceHierarchy[level1Value].children[level2Value].children;
        for (const [key, value] of Object.entries(children)) {
          const option = document.createElement('option');
          option.value = key;
          option.textContent = value.name;
          level3Select.appendChild(option);
        }
        level3Container.classList.remove('hidden');
      } else if (level2Value) {
        // No children, show final selection
        const servicePath = `${serviceHierarchy[level1Value].name} > ${serviceHierarchy[level1Value].children[level2Value].name}`;
        selectedServiceText.textContent = servicePath;
        selectedServiceDisplay.classList.remove('hidden');
      }
    });

    // Level 3 change
    level3Select.addEventListener('change', function() {
      const level1Value = level1Select.value;
      const level2Value = level2Select.value;
      const level3Value = this.value;
      
      selectedServiceDisplay.classList.add('hidden');
      
      if (level3Value) {
        // Show final selection
        const servicePath = `${serviceHierarchy[level1Value].name} > ${serviceHierarchy[level1Value].children[level2Value].name} > ${serviceHierarchy[level1Value].children[level2Value].children[level3Value].name}`;
        selectedServiceText.textContent = servicePath;
        selectedServiceDisplay.classList.remove('hidden');
      }
    });

    // Toggle Service Examples
    const showExamplesBtn = document.getElementById('show-service-examples');
    const serviceExamples = document.getElementById('service-examples');
    if (showExamplesBtn && serviceExamples) {
      showExamplesBtn.addEventListener('click', function() {
        if (serviceExamples.classList.contains('hidden')) {
          serviceExamples.classList.remove('hidden');
          showExamplesBtn.textContent = 'Hide Examples';
        } else {
          serviceExamples.classList.add('hidden');
          showExamplesBtn.textContent = 'Show Examples';
        }
      });
    }

    // Queue data structure
    const allVisitors = [
      { 
        id: 1, 
        token: 'A026', 
        name: 'Michael Chen', 
        service: 'Medical Check', 
        serviceType: 'medical', 
        waitTime: '12 min', 
        type: 'walk-in', 
        badge: 'bg-blue-100 text-blue-700',
        parentService: 'Medical Check',
        subParentService: 'Physical Examination',
        childService: 'Routine Check-up'
      },
      { 
        id: 2, 
        token: 'A027', 
        name: 'Emma Williams', 
        service: 'Lab Results', 
        serviceType: 'lab', 
        waitTime: '8 min', 
        type: 'appointment', 
        badge: 'bg-pink-100 text-pink-700',
        parentService: 'Laboratory Services',
        subParentService: 'Blood Tests',
        childService: 'Complete Blood Count (CBC)'
      },
      { 
        id: 3, 
        token: 'A028', 
        name: 'James Rodriguez', 
        service: 'Prescription', 
        serviceType: 'prescription', 
        waitTime: '5 min', 
        type: 'walk-in', 
        badge: 'bg-green-100 text-green-700',
        parentService: 'Prescription',
        subParentService: 'New Prescription',
        childService: 'Chronic Condition'
      },
      { 
        id: 4, 
        token: 'A029', 
        name: 'Sophie Taylor', 
        service: 'General Consultation', 
        serviceType: 'general', 
        waitTime: '15 min', 
        type: 'appointment', 
        badge: 'bg-purple-100 text-purple-700',
        parentService: 'General Consultation',
        subParentService: null,
        childService: null
      },
      { 
        id: 5, 
        token: 'A030', 
        name: 'David Kim', 
        service: 'Follow-up', 
        serviceType: 'followup', 
        waitTime: '10 min', 
        type: 'walk-in', 
        badge: 'bg-orange-100 text-orange-700',
        parentService: 'Follow-up',
        subParentService: null,
        childService: null
      },
      { 
        id: 6, 
        token: 'A031', 
        name: 'Maria Garcia', 
        service: 'Medical Check', 
        serviceType: 'medical', 
        waitTime: '18 min', 
        type: 'appointment', 
        badge: 'bg-blue-100 text-blue-700',
        parentService: 'Medical Check',
        subParentService: 'Diagnostic Tests',
        childService: 'X-Ray'
      }
    ];

    // Helper function to format service hierarchy
    function formatServiceHierarchy(visitor) {
      if (!visitor.subParentService && !visitor.childService) {
        return `<span class="text-xs font-semibold text-gray-700">${visitor.parentService}</span>`;
      }
      
      let html = `
        <div class="text-xs space-y-1">
          <div class="flex items-center gap-1">
            <span class="w-4 h-4 rounded-full bg-purple-600 text-white flex items-center justify-center text-[8px] font-bold flex-shrink-0">1</span>
            <span class="font-semibold text-gray-900">${visitor.parentService}</span>
          </div>
      `;
      
      if (visitor.subParentService) {
        html += `
          <div class="flex items-center gap-1 ml-2">
            <span class="w-4 h-4 rounded-full bg-purple-500 text-white flex items-center justify-center text-[8px] font-bold flex-shrink-0">2</span>
            <span class="font-medium text-gray-700">${visitor.subParentService}</span>
          </div>
        `;
      }
      
      if (visitor.childService) {
        html += `
          <div class="flex items-center gap-1 ml-4">
            <span class="w-4 h-4 rounded-full bg-purple-400 text-white flex items-center justify-center text-[8px] font-bold flex-shrink-0">3</span>
            <span class="text-gray-600">${visitor.childService}</span>
          </div>
        `;
      }
      
      html += `</div>`;
      return html;
    }

    // Filter and render queue
    function renderQueue() {
      const queueFilter = document.getElementById('queue-filter').value;
      const serviceFilter = document.getElementById('service-filter').value;
      const queueContainer = document.querySelector('.space-y-3');
      
      let filteredVisitors = allVisitors;
      
      // Apply visitor type filter
      if (queueFilter !== 'all') {
        filteredVisitors = filteredVisitors.filter(v => v.type === queueFilter);
      }
      
      // Apply service filter
      if (serviceFilter !== 'all') {
        filteredVisitors = filteredVisitors.filter(v => v.serviceType === serviceFilter);
      }
      
      // Update visitor count
      document.querySelector('.text-xs.font-bold.text-gray-500.uppercase.tracking-wider + span').textContent = `${filteredVisitors.length} visitors`;
      
      // Render filtered visitors
      queueContainer.innerHTML = filteredVisitors.map(visitor => `
        <div class="visitor-card bg-white rounded-2xl p-4 cursor-pointer shadow-md hover:shadow-lg transition-shadow">
          <div class="flex items-center gap-2 mb-3">
            <span class="px-2 py-1 bg-purple-600 text-white text-sm font-bold rounded-md">${visitor.token}</span>
            <span class="px-3 py-1 ${visitor.badge} text-xs font-bold rounded-lg">${visitor.type === 'walk-in' ? 'Walk-in' : 'Appointment'}</span>
          </div>
          <div class="mb-3">
            <h3 class="font-bold text-gray-900 text-base mb-1">${visitor.name}</h3>
            <div class="flex items-center gap-1.5 text-xs text-gray-500">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
              <span class="font-semibold">${visitor.waitTime}</span>
            </div>
          </div>
          <div class="p-2 bg-gray-50 rounded-lg mb-2">
            ${formatServiceHierarchy(visitor)}
          </div>
          <div class="text-xs font-medium text-gray-400 uppercase tracking-wide">Not Assigned</div>
        </div>
      `).join('');
      
      if (filteredVisitors.length === 0) {
        queueContainer.innerHTML = `
          <div class="text-center py-8 text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
            </svg>
            <p class="font-semibold">No visitors found</p>
            <p class="text-sm mt-1">Try adjusting your filters</p>
          </div>
        `;
      }
    }

    // Desk selector functionality
    document.getElementById('desk-select').addEventListener('change', (e) => {
      const deskNumber = e.target.value.replace('desk', '');
      console.log('Switched to Desk ' + deskNumber);
      // In a real application, this would load different queue data
    });

    // Filter change listeners
    document.getElementById('queue-filter').addEventListener('change', renderQueue);
    document.getElementById('service-filter').addEventListener('change', renderQueue);

    // Initial render
    renderQueue();
    
    // More menu toggle functionality
    const moreMenuBtn = document.getElementById('more-menu-btn');
    const moreMenu = document.getElementById('more-menu');
    
    if (moreMenuBtn && moreMenu) {
      moreMenuBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        moreMenu.classList.toggle('hidden');
      });
      
      // Close menu when clicking outside
      document.addEventListener('click', (e) => {
        if (!moreMenu.contains(e.target) && !moreMenuBtn.contains(e.target)) {
          moreMenu.classList.add('hidden');
        }
      });
      
      // Close menu when clicking a menu item
      const menuItems = moreMenu.querySelectorAll('button');
      menuItems.forEach(item => {
        item.addEventListener('click', () => {
          moreMenu.classList.add('hidden');
        });
      });
    }

    // Modal functionality
    const queueModal = document.getElementById('queue-modal');
    const closeModal = document.getElementById('close-modal');
    const modalTitle = document.getElementById('modal-title');
    const modalSubtitle = document.getElementById('modal-subtitle');
    const modalContent = document.getElementById('modal-content');
    
    // Sample data for different queue types
    const queueData = {
      served: [
        { number: 'A024', name: 'Robert Martinez', service: 'General Consultation', time: '11:45 AM', clerk: 'Emily Davis' },
        { number: 'A023', name: 'Lisa Anderson', service: 'Lab Results', time: '11:30 AM', clerk: 'John Smith' },
        { number: 'A022', name: 'David Kim', service: 'Prescription', time: '11:15 AM', clerk: 'Emily Davis' },
        { number: 'A021', name: 'Maria Garcia', service: 'Medical Check', time: '11:00 AM', clerk: 'Sarah Wilson' },
        { number: 'A020', name: 'Thomas Lee', service: 'Follow-up', time: '10:45 AM', clerk: 'John Smith' },
        { number: 'A019', name: 'Jennifer Brown', service: 'General Consultation', time: '10:30 AM', clerk: 'Emily Davis' }
      ],
      missed: [
        { number: 'A018', name: 'Kevin White', service: 'Medical Check', time: '10:15 AM', reason: 'No response after 3 calls' },
        { number: 'A015', name: 'Rachel Green', service: 'Lab Results', time: '9:45 AM', reason: 'Left before being called' },
        { number: 'A012', name: 'Mark Thompson', service: 'Prescription', time: '9:20 AM', reason: 'Cancelled via app' }
      ],
      hold: [
        { number: 'A017', name: 'Patricia Wilson', service: 'General Consultation', time: '10:10 AM', clerk: 'Emily Davis', reason: 'Waiting for documents' },
        { number: 'A016', name: 'Daniel Moore', service: 'Follow-up', time: '10:00 AM', clerk: 'John Smith', reason: 'Technical issues' }
      ]
    };
    
    function openModal(type) {
      const data = queueData[type];
      let title = '';
      let subtitle = '';
      let borderColor = '';
      let bgGradient = '';
      
      if (type === 'served') {
        title = 'Served Queues';
        subtitle = `${data.length} visitors completed today`;
        borderColor = 'border-green-500';
        bgGradient = 'linear-gradient(135deg, #10b981, #059669)';
      } else if (type === 'missed') {
        title = 'Missed Queues';
        subtitle = `${data.length} visitors missed today`;
        borderColor = 'border-red-500';
        bgGradient = 'linear-gradient(135deg, #ef4444, #dc2626)';
      } else if (type === 'hold') {
        title = 'Hold Queues';
        subtitle = `${data.length} visitors on hold`;
        borderColor = 'border-orange-500';
        bgGradient = 'linear-gradient(135deg, #f59e0b, #d97706)';
      }
      
      modalTitle.textContent = title;
      modalSubtitle.textContent = subtitle;
      
      // Generate queue items
      modalContent.innerHTML = data.map(item => {
        let actionButtons = '';
        
        if (type === 'served') {
          actionButtons = `
            <button class="px-4 py-2 bg-purple-100 hover:bg-purple-200 text-purple-700 text-sm font-semibold rounded-lg transition-all">
              View Details
            </button>
          `;
        } else if (type === 'missed') {
          actionButtons = `
            <button class="px-4 py-2 text-white text-sm font-semibold rounded-lg transition-all hover:shadow-lg" style="background: ${bgGradient};">
              Recall
            </button>
            <button class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-all">
              Move to Waiting
            </button>
          `;
        } else if (type === 'hold') {
          actionButtons = `
            <button class="px-4 py-2 text-white text-sm font-semibold rounded-lg transition-all hover:shadow-lg" style="background: ${bgGradient};">
              Resume
            </button>
            <button class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold rounded-lg transition-all">
              Move to Waiting
            </button>
          `;
        }
        
        return `
          <div class="bg-white border-2 border-gray-200 rounded-xl p-4 hover:shadow-md transition-all border-l-4 ${borderColor}">
            <div class="flex items-start justify-between mb-3">
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                  <span class="text-xl font-bold text-gray-900">${item.number}</span>
                  <span class="px-3 py-1 bg-gray-100 text-gray-700 text-xs font-bold rounded-lg">${item.service}</span>
                </div>
                <h3 class="font-bold text-gray-900 text-base">${item.name}</h3>
                <div class="flex items-center gap-3 mt-2 text-sm text-gray-600">
                  <span class="flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    ${item.time}
                  </span>
                  ${item.clerk ? `
                    <span class="flex items-center gap-1">
                      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                      </svg>
                      ${item.clerk}
                    </span>
                  ` : ''}
                </div>
                ${item.reason ? `
                  <p class="text-sm text-gray-500 mt-2 italic">Reason: ${item.reason}</p>
                ` : ''}
              </div>
            </div>
            <div class="flex gap-2 mt-3">
              ${actionButtons}
            </div>
          </div>
        `;
      }).join('');
      
      queueModal.classList.remove('hidden');
    }
    
    function closeModalFunc() {
      queueModal.classList.add('hidden');
    }
    
    // Add click handlers for queue cards
    document.getElementById('served-card').addEventListener('click', () => openModal('served'));
    document.getElementById('missed-card').addEventListener('click', () => openModal('missed'));
    document.getElementById('hold-card').addEventListener('click', () => openModal('hold'));
    
    // Close modal handlers
    closeModal.addEventListener('click', closeModalFunc);
    queueModal.addEventListener('click', (e) => {
      if (e.target === queueModal) {
        closeModalFunc();
      }
    });

    // Tab Switching Functionality
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabButtons.forEach(button => {
      button.addEventListener('click', () => {
        const tabName = button.getAttribute('data-tab');
        
        // Remove active class from all buttons
        tabButtons.forEach(btn => {
          btn.classList.remove('active', 'border-purple-600', 'text-purple-600');
          btn.classList.add('border-transparent', 'text-gray-500');
        });
        
        // Add active class to clicked button
        button.classList.add('active', 'border-purple-600', 'text-purple-600');
        button.classList.remove('border-transparent', 'text-gray-500');
        
        // Hide all tab contents
        tabContents.forEach(content => {
          content.classList.add('hidden');
        });
        
        // Show selected tab content
        document.getElementById(`tab-${tabName}`).classList.remove('hidden');
      });
    });

    // Activity Panel Toggle
    const activityPanel = document.getElementById('activity-panel');
    const activityContent = document.getElementById('activity-content');
    const toggleButton = document.getElementById('toggle-activity-log');
    const leftPanel = document.getElementById('left-panel');
    let isActivityLogOpen = true;

    toggleButton.addEventListener('click', () => {
      isActivityLogOpen = !isActivityLogOpen;
      
      const tabsNav = activityPanel.querySelector('.border-b.flex-shrink-0:not(.p-4)');
      const headerTitle = activityPanel.querySelector('h2');
      
      if (isActivityLogOpen) {
        // Open the panel
        activityPanel.style.width = '20rem'; // w-80 = 20rem
        activityPanel.style.minWidth = '20rem';
        activityContent.classList.remove('hidden');
        if (tabsNav) tabsNav.classList.remove('hidden');
        if (headerTitle) headerTitle.classList.remove('hidden');
        toggleButton.querySelector('svg').style.transform = 'rotate(0deg)';
        // Reset left panel to normal size
        leftPanel.style.width = '20rem'; // w-80
      } else {
        // Close the panel
        activityPanel.style.width = '48px'; // Just show the toggle button
        activityPanel.style.minWidth = '48px';
        activityContent.classList.add('hidden');
        if (tabsNav) tabsNav.classList.add('hidden');
        if (headerTitle) headerTitle.classList.add('hidden');
        toggleButton.querySelector('svg').style.transform = 'rotate(180deg)';
        // Expand left panel to use the extra space
        leftPanel.style.width = '28rem'; // Expand from 20rem to 28rem (extra 8rem)
      }
    });

    // Save Visitor Information
    const saveVisitorBtn = document.getElementById('save-visitor-btn');
    if (saveVisitorBtn) {
      saveVisitorBtn.addEventListener('click', (e) => {
        e.preventDefault();
        
        // Show success message
        const originalText = saveVisitorBtn.innerHTML;
        saveVisitorBtn.innerHTML = '<svg class="w-5 h-5 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg> Saving...';
        
        // Simulate save (replace with actual save logic)
        setTimeout(() => {
          saveVisitorBtn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Saved Successfully!';
          saveVisitorBtn.style.background = 'linear-gradient(to right, #10b981, #059669)';
          
          setTimeout(() => {
            saveVisitorBtn.innerHTML = originalText;
            saveVisitorBtn.style.background = '';
          }, 2000);
        }, 1000);
      });
    }
  </script>
</body>
</html>

