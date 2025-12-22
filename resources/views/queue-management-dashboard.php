<!doctype html>
<html lang="en" class="h-full">
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
      left: 11px;
      top: 28px;
      bottom: -12px;
      width: 3px;
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
  </style>
  <style>@view-transition { navigation: auto; }</style>
  <script src="/_sdk/data_sdk.js" type="text/javascript"></script>
  <script src="/_sdk/element_sdk.js" type="text/javascript"></script>
 </head>
 <body class="h-full w-full" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
  <div class="flex flex-col h-full w-full">
   <div class="flex flex-1 overflow-hidden min-h-0"><!-- Left Sidebar -->
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
    <div class="w-80 glass-effect border-r" style="border-color: rgba(139, 92, 246, 0.1);">
     <div class="flex flex-col h-full">
      <div class="p-6 border-b" style="border-color: rgba(139, 92, 246, 0.1);">
       <h1 id="system-title" class="text-xl font-bold text-gray-900">Queue Management System</h1>
       <div class="mt-3"><label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Active Station</label> <select id="desk-select" class="w-full px-4 py-2.5 border-2 border-purple-200 rounded-xl text-sm font-bold text-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all bg-white"> <option value="desk1">Desk 1</option> <option value="desk2">Desk 2</option> <option value="desk3" selected>Desk 3</option> <option value="desk4">Desk 4</option> <option value="desk5">Desk 5</option> </select>
       </div>
      </div>
      <div class="flex-1 overflow-auto p-6 space-y-6"><!-- Serving Now -->
       <div>
        <div class="flex items-center justify-between mb-3">
         <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Currently Serving</h2><span class="stat-badge px-2 py-0.5 bg-green-100 text-green-700 text-xs font-bold rounded-full">● Active</span>
        </div>
        <div class="visitor-card active rounded-2xl p-5 shadow-lg" style="background: linear-gradient(135deg, rgba(139, 92, 246, 0.1), rgba(236, 72, 153, 0.1));">
         <div class="flex items-start justify-between mb-3">
          <div>
           <h3 id="serving-name" class="font-bold text-gray-900 text-lg">Sarah Johnson</h3>
           <p class="text-sm text-gray-600 mt-1 flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg> 15 Mar 1985</p>
          </div><span id="serving-badge" class="px-3 py-1.5 text-white text-xs font-bold rounded-xl shadow-md" style="background: linear-gradient(135deg, #8b5cf6, #ec4899);">General Consultation</span>
         </div>
         <div class="flex items-center text-sm text-gray-700 bg-white rounded-lg px-3 py-2">
          <svg class="w-4 h-4 mr-2" style="color: #8b5cf6;" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
          </svg><span class="font-medium">Emily Davis</span>
         </div>
        </div>
       </div><!-- Waiting Queue -->
       <div>
        <div class="flex items-center justify-between mb-3">
         <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Waiting Queue</h2><span class="px-2.5 py-1 bg-purple-100 text-purple-700 text-xs font-bold rounded-full">6 visitors</span>
        </div><!-- Filter Dropdowns -->
        <div class="mb-4 space-y-3"><select id="queue-filter" class="w-full px-3 py-2 border-2 border-purple-200 rounded-xl text-sm font-bold text-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all bg-white"> <option value="all">All</option> <option value="walk-in">Walk-in</option> <option value="appointment">Appointment</option> </select> <select id="service-filter" class="w-full px-3 py-2 border-2 border-purple-200 rounded-xl text-sm font-bold text-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all bg-white"> <option value="all">All Services</option> <option value="general">General Consultation</option> <option value="medical">Medical Check</option> <option value="lab">Lab Results</option> <option value="prescription">Prescription</option> <option value="followup">Follow-up</option> </select>
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
      <div class="p-6 border-t" style="border-color: rgba(139, 92, 246, 0.1);"><button class="w-full text-white font-bold py-3.5 px-4 rounded-xl transition-all flex items-center justify-center shadow-lg hover:shadow-xl" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
        </svg> Add New Visitor </button>
      </div>
     </div>
    </div><!-- Middle Panel - Visitor Details -->
    <div class="flex-1 flex flex-col overflow-auto" style="background: rgba(249, 250, 251, 0.5);">
     <div class="glass-effect border-b p-6" style="border-color: rgba(139, 92, 246, 0.1);">
      <div class="flex items-center justify-between">
       <div>
        <h1 id="detail-name" class="text-3xl font-bold text-gray-900">Sarah Johnson</h1>
        <div class="flex items-center gap-3 mt-3"><span class="px-4 py-1.5 text-white text-sm font-bold rounded-xl" style="background: linear-gradient(135deg, #10b981, #059669);">● Serving now</span> <span class="px-3 py-1.5 bg-gray-100 text-gray-700 text-sm font-semibold rounded-lg">1h 2m</span>
        </div>
       </div><button class="p-3 hover:bg-gray-100 rounded-xl transition-all">
        <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
        </svg></button>
      </div>
     </div>
     <div class="flex-1 p-6 overflow-auto">
      <div class="max-w-3xl mx-auto">
       <div class="bg-white rounded-2xl shadow-lg p-8">
        <h2 class="text-lg font-bold text-gray-900 mb-6 flex items-center gap-2">
         <svg class="w-5 h-5" style="color: #8b5cf6;" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
         </svg> Visitor Information</h2>
        <form class="space-y-5">
         <div class="grid grid-cols-2 gap-5">
          <div><label class="block text-sm font-bold text-gray-700 mb-2">First name</label> <input type="text" value="Sarah" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all font-medium">
          </div>
          <div><label class="block text-sm font-bold text-gray-700 mb-2">Last name</label> <input type="text" value="Johnson" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all font-medium">
          </div>
         </div>
         <div><label class="block text-sm font-bold text-gray-700 mb-2">Email address</label> <input type="email" value="sarah.johnson@email.com" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all font-medium">
         </div>
         <div><label class="block text-sm font-bold text-gray-700 mb-2">Phone number</label> <input type="tel" value="+1 (555) 123-4567" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all font-medium">
         </div>
         <div class="grid grid-cols-2 gap-5">
          <div><label class="block text-sm font-bold text-gray-700 mb-2">Preferred Language</label> <select class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all bg-white font-medium"> <option>English</option> <option>Spanish</option> <option>French</option> <option>German</option> <option>Chinese</option> </select>
          </div>
          <div><label class="block text-sm font-bold text-gray-700 mb-2">Service Type</label> <select id="service-select" class="w-full px-4 py-3 border-2 border-gray-200 rounded-xl text-gray-900 focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all bg-white font-medium"> <option>General Consultation</option> <option>Medical Check</option> <option>Lab Results</option> <option>Prescription</option> <option>Follow-up</option> </select>
          </div>
         </div>
        </form>
       </div>
      </div>
     </div>
     <div class="glass-effect border-t p-6" style="border-color: rgba(139, 92, 246, 0.1);">
      <div class="max-w-3xl mx-auto space-y-3">
       <div class="flex items-center gap-3"><button id="finish-btn" class="flex-1 text-white font-bold py-4 px-6 rounded-xl transition-all shadow-lg hover:shadow-xl" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);"> Complete Service </button> <button id="call-next-btn" class="flex-1 bg-white border-2 border-purple-200 hover:border-purple-300 text-gray-700 font-bold py-4 px-6 rounded-xl transition-all shadow-md hover:shadow-lg"> Next Visitor </button>
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
    </div><!-- Right Panel - Activity Timeline -->
    <div class="w-80 glass-effect border-l flex flex-col overflow-auto" style="border-color: rgba(139, 92, 246, 0.1);">
     <div class="p-6 border-b" style="border-color: rgba(139, 92, 246, 0.1);">
      <div class="flex items-center justify-between">
       <h2 class="text-lg font-bold text-gray-900 flex items-center gap-2">
        <svg class="w-5 h-5" style="color: #8b5cf6;" fill="none" stroke="currentColor" viewbox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg> Activity Log</h2><span class="px-3 py-1.5 bg-purple-100 text-purple-700 text-xs font-bold rounded-lg">1h 2m</span>
      </div>
     </div>
     <div class="flex-1 overflow-auto p-6">
      <div class="space-y-6">
       <div class="timeline-item relative pl-10">
        <div class="absolute left-0 top-1 w-6 h-6 rounded-full border-4 border-white shadow-lg" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);"></div>
        <div class="text-sm">
         <p class="font-bold text-gray-900">Called by Clerk</p>
         <p class="text-gray-600 mt-1">Emily Davis initiated service</p>
         <p class="text-gray-400 text-xs mt-2 font-semibold">10:23 AM</p>
        </div>
       </div>
       <div class="timeline-item relative pl-10">
        <div class="absolute left-0 top-1 w-6 h-6 rounded-full border-4 border-white shadow-lg" style="background: linear-gradient(135deg, #06b6d4, #0891b2);"></div>
        <div class="text-sm">
         <p class="font-bold text-gray-900">SMS Sent - Called</p>
         <p class="text-gray-600 mt-1">Notification sent to +1 (555) 123-4567</p>
         <p class="text-gray-500 text-xs mt-2 bg-gray-50 rounded-lg p-2 font-medium">"Your turn! Please proceed to Desk 3 now."</p>
         <p class="text-gray-400 text-xs mt-2 font-semibold">10:23 AM</p>
        </div>
       </div>
       <div class="timeline-item relative pl-10">
        <div class="absolute left-0 top-1 w-6 h-6 rounded-full border-4 border-white shadow-lg" style="background: linear-gradient(135deg, #3b82f6, #2563eb);"></div>
        <div class="text-sm">
         <p class="font-bold text-gray-900">Desk Assignment</p>
         <p class="text-gray-600 mt-1">Auto-assigned to Desk 3</p>
         <p class="text-gray-400 text-xs mt-2 font-semibold">10:22 AM</p>
        </div>
       </div>
       <div class="timeline-item relative pl-10">
        <div class="absolute left-0 top-1 w-6 h-6 rounded-full border-4 border-white shadow-lg" style="background: linear-gradient(135deg, #10b981, #059669);"></div>
        <div class="text-sm">
         <p class="font-bold text-gray-900">Check-in Complete</p>
         <p class="text-gray-600 mt-1">Self-service kiosk registration</p>
         <p class="text-gray-400 text-xs mt-2 font-semibold">9:21 AM</p>
        </div>
       </div>
       <div class="timeline-item relative pl-10">
        <div class="absolute left-0 top-1 w-6 h-6 rounded-full border-4 border-white shadow-lg" style="background: linear-gradient(135deg, #06b6d4, #0891b2);"></div>
        <div class="text-sm">
         <p class="font-bold text-gray-900">SMS Sent - Queue Created</p>
         <p class="text-gray-600 mt-1">Confirmation sent to +1 (555) 123-4567</p>
         <p class="text-gray-500 text-xs mt-2 bg-gray-50 rounded-lg p-2 font-medium">"Queue A025 confirmed. You are 8th in line. Estimated wait: 35 min."</p>
         <p class="text-gray-400 text-xs mt-2 font-semibold">9:21 AM</p>
        </div>
       </div>
       <div class="timeline-item relative pl-10">
        <div class="absolute left-0 top-1 w-6 h-6 rounded-full border-4 border-white shadow-lg" style="background: linear-gradient(135deg, #ec4899, #db2777);"></div>
        <div class="text-sm">
         <p class="font-bold text-gray-900">Appointment Booked</p>
         <p class="text-gray-600 mt-1">Online scheduling system</p>
         <p class="text-gray-400 text-xs mt-2 font-semibold">Yesterday, 3:45 PM</p>
        </div>
       </div>
      </div>
     </div>
    </div>
   </div>
  </div><!-- Bottom Status Bar -->
  <div class="border-t" style="border-color: rgba(139, 92, 246, 0.2); background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(10px);">
   <div class="flex"><!-- Extended Sidebar Space -->
    <div class="w-20" style="background: linear-gradient(180deg, rgba(139, 92, 246, 0.95) 0%, rgba(109, 40, 217, 0.95) 100%);"></div><!-- Stats Content -->
    <div class="flex-1 px-6 py-3">
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
    // Queue data structure
    const allVisitors = [
      { id: 1, name: 'Michael Chen', service: 'Medical Check', serviceType: 'medical', waitTime: '12 min', type: 'walk-in', badge: 'bg-blue-100 text-blue-700' },
      { id: 2, name: 'Emma Williams', service: 'Lab Results', serviceType: 'lab', waitTime: '8 min', type: 'appointment', badge: 'bg-pink-100 text-pink-700' },
      { id: 3, name: 'James Rodriguez', service: 'Prescription', serviceType: 'prescription', waitTime: '5 min', type: 'walk-in', badge: 'bg-green-100 text-green-700' },
      { id: 4, name: 'Sophie Taylor', service: 'General Consultation', serviceType: 'general', waitTime: '15 min', type: 'appointment', badge: 'bg-purple-100 text-purple-700' },
      { id: 5, name: 'David Kim', service: 'Follow-up', serviceType: 'followup', waitTime: '10 min', type: 'walk-in', badge: 'bg-orange-100 text-orange-700' },
      { id: 6, name: 'Maria Garcia', service: 'Medical Check', serviceType: 'medical', waitTime: '18 min', type: 'appointment', badge: 'bg-blue-100 text-blue-700' }
    ];

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
        <div class="visitor-card bg-white rounded-2xl p-4 cursor-pointer shadow-md">
          <div class="flex items-start justify-between mb-3">
            <div>
              <h3 class="font-bold text-gray-900 text-base">${visitor.name}</h3>
              <div class="flex items-center gap-1.5 text-xs text-gray-500 mt-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="font-semibold">${visitor.waitTime}</span>
              </div>
            </div>
            <span class="px-3 py-1 ${visitor.badge} text-xs font-bold rounded-lg">${visitor.service}</span>
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
  </script>
 <script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9b1f1b3f27ad594e',t:'MTc2NjQwMTEzMS4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script></body>
</html>