<html lang="en" class="h-full"><head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Subscription Management Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    body {
      box-sizing: border-box;
    }
    
    * {
      box-sizing: border-box;
    }

    .modal-backdrop {
      backdrop-filter: blur(4px);
    }

    .slide-in {
      animation: slideIn 0.3s ease-out;
    }

    @keyframes slideIn {
      from {
        opacity: 0;
        transform: translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .tab-active {
      border-bottom: 3px solid;
    }

    .spinner {
      border: 3px solid rgba(255, 255, 255, 0.3);
      border-top-color: white;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      animation: spin 0.8s linear infinite;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }
  </style>
  <style>@view-transition { navigation: auto; }</style>
  <script src="/_sdk/data_sdk.js" type="text/javascript"></script>
  <script src="/_sdk/element_sdk.js" type="text/javascript"></script>
 <style>*, ::before, ::after{--tw-border-spacing-x:0;--tw-border-spacing-y:0;--tw-translate-x:0;--tw-translate-y:0;--tw-rotate:0;--tw-skew-x:0;--tw-skew-y:0;--tw-scale-x:1;--tw-scale-y:1;--tw-pan-x: ;--tw-pan-y: ;--tw-pinch-zoom: ;--tw-scroll-snap-strictness:proximity;--tw-gradient-from-position: ;--tw-gradient-via-position: ;--tw-gradient-to-position: ;--tw-ordinal: ;--tw-slashed-zero: ;--tw-numeric-figure: ;--tw-numeric-spacing: ;--tw-numeric-fraction: ;--tw-ring-inset: ;--tw-ring-offset-width:0px;--tw-ring-offset-color:#fff;--tw-ring-color:rgb(59 130 246 / 0.5);--tw-ring-offset-shadow:0 0 #0000;--tw-ring-shadow:0 0 #0000;--tw-shadow:0 0 #0000;--tw-shadow-colored:0 0 #0000;--tw-blur: ;--tw-brightness: ;--tw-contrast: ;--tw-grayscale: ;--tw-hue-rotate: ;--tw-invert: ;--tw-saturate: ;--tw-sepia: ;--tw-drop-shadow: ;--tw-backdrop-blur: ;--tw-backdrop-brightness: ;--tw-backdrop-contrast: ;--tw-backdrop-grayscale: ;--tw-backdrop-hue-rotate: ;--tw-backdrop-invert: ;--tw-backdrop-opacity: ;--tw-backdrop-saturate: ;--tw-backdrop-sepia: ;--tw-contain-size: ;--tw-contain-layout: ;--tw-contain-paint: ;--tw-contain-style: }::backdrop{--tw-border-spacing-x:0;--tw-border-spacing-y:0;--tw-translate-x:0;--tw-translate-y:0;--tw-rotate:0;--tw-skew-x:0;--tw-skew-y:0;--tw-scale-x:1;--tw-scale-y:1;--tw-pan-x: ;--tw-pan-y: ;--tw-pinch-zoom: ;--tw-scroll-snap-strictness:proximity;--tw-gradient-from-position: ;--tw-gradient-via-position: ;--tw-gradient-to-position: ;--tw-ordinal: ;--tw-slashed-zero: ;--tw-numeric-figure: ;--tw-numeric-spacing: ;--tw-numeric-fraction: ;--tw-ring-inset: ;--tw-ring-offset-width:0px;--tw-ring-offset-color:#fff;--tw-ring-color:rgb(59 130 246 / 0.5);--tw-ring-offset-shadow:0 0 #0000;--tw-ring-shadow:0 0 #0000;--tw-shadow:0 0 #0000;--tw-shadow-colored:0 0 #0000;--tw-blur: ;--tw-brightness: ;--tw-contrast: ;--tw-grayscale: ;--tw-hue-rotate: ;--tw-invert: ;--tw-saturate: ;--tw-sepia: ;--tw-drop-shadow: ;--tw-backdrop-blur: ;--tw-backdrop-brightness: ;--tw-backdrop-contrast: ;--tw-backdrop-grayscale: ;--tw-backdrop-hue-rotate: ;--tw-backdrop-invert: ;--tw-backdrop-opacity: ;--tw-backdrop-saturate: ;--tw-backdrop-sepia: ;--tw-contain-size: ;--tw-contain-layout: ;--tw-contain-paint: ;--tw-contain-style: }/* ! tailwindcss v3.4.17 | MIT License | https://tailwindcss.com */*,::after,::before{box-sizing:border-box;border-width:0;border-style:solid;border-color:#e5e7eb}::after,::before{--tw-content:''}:host,html{line-height:1.5;-webkit-text-size-adjust:100%;-moz-tab-size:4;tab-size:4;font-family:ui-sans-serif, system-ui, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";font-feature-settings:normal;font-variation-settings:normal;-webkit-tap-highlight-color:transparent}body{margin:0;line-height:inherit}hr{height:0;color:inherit;border-top-width:1px}abbr:where([title]){-webkit-text-decoration:underline dotted;text-decoration:underline dotted}h1,h2,h3,h4,h5,h6{font-size:inherit;font-weight:inherit}a{color:inherit;text-decoration:inherit}b,strong{font-weight:bolder}code,kbd,pre,samp{font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;font-feature-settings:normal;font-variation-settings:normal;font-size:1em}small{font-size:80%}sub,sup{font-size:75%;line-height:0;position:relative;vertical-align:baseline}sub{bottom:-.25em}sup{top:-.5em}table{text-indent:0;border-color:inherit;border-collapse:collapse}button,input,optgroup,select,textarea{font-family:inherit;font-feature-settings:inherit;font-variation-settings:inherit;font-size:100%;font-weight:inherit;line-height:inherit;letter-spacing:inherit;color:inherit;margin:0;padding:0}button,select{text-transform:none}button,input:where([type=button]),input:where([type=reset]),input:where([type=submit]){-webkit-appearance:button;background-color:transparent;background-image:none}:-moz-focusring{outline:auto}:-moz-ui-invalid{box-shadow:none}progress{vertical-align:baseline}::-webkit-inner-spin-button,::-webkit-outer-spin-button{height:auto}[type=search]{-webkit-appearance:textfield;outline-offset:-2px}::-webkit-search-decoration{-webkit-appearance:none}::-webkit-file-upload-button{-webkit-appearance:button;font:inherit}summary{display:list-item}blockquote,dd,dl,figure,h1,h2,h3,h4,h5,h6,hr,p,pre{margin:0}fieldset{margin:0;padding:0}legend{padding:0}menu,ol,ul{list-style:none;margin:0;padding:0}dialog{padding:0}textarea{resize:vertical}input::placeholder,textarea::placeholder{opacity:1;color:#9ca3af}[role=button],button{cursor:pointer}:disabled{cursor:default}audio,canvas,embed,iframe,img,object,svg,video{display:block;vertical-align:middle}img,video{max-width:100%;height:auto}[hidden]:where(:not([hidden=until-found])){display:none}.mx-auto{margin-left:auto;margin-right:auto}.mb-6{margin-bottom:1.5rem}.mt-6{margin-top:1.5rem}.flex{display:flex}.h-full{height:100%}.min-h-full{min-height:100%}.w-full{width:100%}.max-w-7xl{max-width:80rem}.items-center{align-items:center}.justify-between{justify-content:space-between}.gap-8{gap:2rem}.overflow-auto{overflow:auto}.border-b{border-bottom-width:1px}.px-4{padding-left:1rem;padding-right:1rem}.px-6{padding-left:1.5rem;padding-right:1.5rem}.py-3{padding-top:0.75rem;padding-bottom:0.75rem}.py-6{padding-top:1.5rem;padding-bottom:1.5rem}.py-8{padding-top:2rem;padding-bottom:2rem}.shadow-sm{--tw-shadow:0 1px 2px 0 rgb(0 0 0 / 0.05);--tw-shadow-colored:0 1px 2px 0 var(--tw-shadow-color);box-shadow:var(--tw-ring-offset-shadow, 0 0 #0000), var(--tw-ring-shadow, 0 0 #0000), var(--tw-shadow)}</style></head>
 <body class="h-full">
  <div id="app" class="h-full w-full overflow-auto" style="background-color: rgb(248, 250, 252); color: rgb(30, 41, 59); font-family: Inter, system-ui, -apple-system, sans-serif;">
        <div class="min-h-full" style="background-color: #f8fafc;">
          <!-- Header -->
          <div class="shadow-sm" style="background-color: #ffffff;">
            <div class="max-w-7xl mx-auto px-6 py-6">
              <div class="flex items-center justify-between">
                <div>
                  <h1 style="font-size: 28px; color: #1e293b; font-weight: 700; margin: 0;">
                    Subscription Management
                  </h1>
                  <p style="font-size: 14px; color: #64748b; margin-top: 4px;">
                    Manage your subscription features and packages
                  </p>
                </div>
                <div style="display: flex; align-items: center; gap: 16px;">
                  <button id="copy-code-btn" style="background-color: #10b981; color: white; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    📋 Copy HTML Code
                  </button>
                  <button id="help-btn" style="background-color: #64748b; color: white; padding: 10px 20px; border-radius: 8px; font-size: 14px; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    📖 Help & Guide
                  </button>
                  <div style="background-color: #3b82f6; color: white; padding: 8px 16px; border-radius: 50%; font-size: 20px; font-weight: 600;">
                    SA
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Navigation Tabs -->
          <div class="max-w-7xl mx-auto px-6 mt-6">
            <div class="flex gap-8 border-b" style="border-color: #e2e8f0;">
              
        <button class="tab-button px-4 py-3 tab-active" data-view="features" style="font-size: 16px; color: #3b82f6; font-weight: 600; border-color: #3b82f6; background: none; border: none; cursor: pointer; border-bottom-width: 3px;">
          📋 Features
        </button>
      
              
        <button class="tab-button px-4 py-3 " data-view="packages" style="font-size: 16px; color: #64748b; font-weight: 500; border-color: #3b82f6; background: none; border: none; cursor: pointer; border-bottom-width: 0;">
          📦 Packages
        </button>
      
              
        <button class="tab-button px-4 py-3 " data-view="configuration" style="font-size: 16px; color: #64748b; font-weight: 500; border-color: #3b82f6; background: none; border: none; cursor: pointer; border-bottom-width: 0;">
          ⚙�� Configuration
        </button>
      
              
        <button class="tab-button px-4 py-3 " data-view="pricing" style="font-size: 16px; color: #64748b; font-weight: 500; border-color: #3b82f6; background: none; border: none; cursor: pointer; border-bottom-width: 0;">
          ��� Pricing
        </button>
      
            </div>
          </div>

          <!-- Main Content -->
          <div class="max-w-7xl mx-auto px-6 py-8">
            <div id="content-area">
        <div>
          <div class="flex justify-between items-center mb-6">
            <div>
              <h2 style="font-size: 24px; color: #1e293b; font-weight: 700; margin: 0 0 8px 0;">
                Subscription Features
              </h2>
              <p style="font-size: 14px; color: #64748b; margin: 0;">
                Define features that will be available in your subscription packages
              </p>
            </div>
            <button id="add-feature-btn" style="background-color: #3b82f6; color: white; padding: 12px 24px; border-radius: 8px; font-size: 16px; font-weight: 600; border: none; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
              + Add Feature
            </button>
          </div>

          <!-- Info Box -->
          <div style="background-color: #eff6ff; border-left: 4px solid #3b82f6; padding: 16px 20px; border-radius: 8px; margin-bottom: 24px;">
            <div style="display: flex; gap: 12px;">
              <div style="font-size: 20px;">ℹ️</div>
              <div>
                <h4 style="font-size: 16px; font-weight: 600; margin: 0 0 8px 0; color: #1e293b;">Quick Tips</h4>
                <ul style="margin: 0; padding-left: 20px; font-size: 14px; color: #1e293b; line-height: 1.8;">
                  <li><strong>Feature Key</strong> is auto-generated and used in your system code</li>
                  <li>Use <strong>Number</strong> type for countable limits (projects, users, storage GB)</li>
                  <li>Use <strong>Boolean</strong> for on/off features (API access, custom domain)</li>
                  <li>Enable <strong>Metered</strong> only if you track usage for billing purposes</li>
                  <li>Set to <strong>Inactive</strong> to temporarily hide without deleting</li>
                </ul>
              </div>
            </div>
          </div>

          <div style="background-color: #ffffff; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
              <thead style="background-color: #f8fafc;">
                <tr>
                  <th style="padding: 16px; text-align: left; font-size: 14px; font-weight: 600; color: #64748b;">Feature Name</th>
                  <th style="padding: 16px; text-align: left; font-size: 14px; font-weight: 600; color: #64748b;">Feature Key</th>
                  <th style="padding: 16px; text-align: left; font-size: 14px; font-weight: 600; color: #64748b;">Data Type</th>
                  <th style="padding: 16px; text-align: left; font-size: 14px; font-weight: 600; color: #64748b;">Metered</th>
                  <th style="padding: 16px; text-align: left; font-size: 14px; font-weight: 600; color: #64748b;">Status</th>
                  <th style="padding: 16px; text-align: right; font-size: 14px; font-weight: 600; color: #64748b;">Actions</th>
                </tr>
              </thead>
              <tbody>
                
                  <tr style="border-top: 1px solid #e2e8f0;">
                    <td style="padding: 16px; font-size: 16px; color: #1e293b; font-weight: 600;">Projects</td>
                    <td style="padding: 16px; font-size: 14px; color: #64748b; font-family: monospace;">projects</td>
                    <td style="padding: 16px; font-size: 14px;">
                      <span style="background-color: #e0e7ff; color: #4338ca; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                        Number
                      </span>
                    </td>
                    <td style="padding: 16px; font-size: 14px;">
                      ❌ No
                    </td>
                    <td style="padding: 16px; font-size: 14px;">
                      <span style="background-color: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                        Active
                      </span>
                    </td>
                    <td style="padding: 16px; text-align: right;">
                      <button class="edit-feature-btn" data-id="id_1766402307416_r9nt8niz5" style="color: #3b82f6; background: none; border: none; cursor: pointer; margin-right: 12px; font-size: 14px; font-weight: 600;">Edit</button>
                      <button class="delete-feature-btn" data-id="id_1766402307416_r9nt8niz5" style="color: #dc2626; background: none; border: none; cursor: pointer; font-size: 14px; font-weight: 600;">Delete</button>
                    </td>
                  </tr>
                
                  <tr style="border-top: 1px solid #e2e8f0;">
                    <td style="padding: 16px; font-size: 16px; color: #1e293b; font-weight: 600;">Storage (GB)</td>
                    <td style="padding: 16px; font-size: 14px; color: #64748b; font-family: monospace;">storage_gb</td>
                    <td style="padding: 16px; font-size: 14px;">
                      <span style="background-color: #e0e7ff; color: #4338ca; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                        Number
                      </span>
                    </td>
                    <td style="padding: 16px; font-size: 14px;">
                      ✅ Yes
                    </td>
                    <td style="padding: 16px; font-size: 14px;">
                      <span style="background-color: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                        Active
                      </span>
                    </td>
                    <td style="padding: 16px; text-align: right;">
                      <button class="edit-feature-btn" data-id="id_1766402307416_fwdnv0dat" style="color: #3b82f6; background: none; border: none; cursor: pointer; margin-right: 12px; font-size: 14px; font-weight: 600;">Edit</button>
                      <button class="delete-feature-btn" data-id="id_1766402307416_fwdnv0dat" style="color: #dc2626; background: none; border: none; cursor: pointer; font-size: 14px; font-weight: 600;">Delete</button>
                    </td>
                  </tr>
                
                  <tr style="border-top: 1px solid #e2e8f0;">
                    <td style="padding: 16px; font-size: 16px; color: #1e293b; font-weight: 600;">Team Members</td>
                    <td style="padding: 16px; font-size: 14px; color: #64748b; font-family: monospace;">team_members</td>
                    <td style="padding: 16px; font-size: 14px;">
                      <span style="background-color: #e0e7ff; color: #4338ca; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                        Number
                      </span>
                    </td>
                    <td style="padding: 16px; font-size: 14px;">
                      ❌ No
                    </td>
                    <td style="padding: 16px; font-size: 14px;">
                      <span style="background-color: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                        Active
                      </span>
                    </td>
                    <td style="padding: 16px; text-align: right;">
                      <button class="edit-feature-btn" data-id="id_1766402307416_q9w4ugi7x" style="color: #3b82f6; background: none; border: none; cursor: pointer; margin-right: 12px; font-size: 14px; font-weight: 600;">Edit</button>
                      <button class="delete-feature-btn" data-id="id_1766402307416_q9w4ugi7x" style="color: #dc2626; background: none; border: none; cursor: pointer; font-size: 14px; font-weight: 600;">Delete</button>
                    </td>
                  </tr>
                
                  <tr style="border-top: 1px solid #e2e8f0;">
                    <td style="padding: 16px; font-size: 16px; color: #1e293b; font-weight: 600;">API Access</td>
                    <td style="padding: 16px; font-size: 14px; color: #64748b; font-family: monospace;">api_access</td>
                    <td style="padding: 16px; font-size: 14px;">
                      <span style="background-color: #e0e7ff; color: #4338ca; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                        Boolean
                      </span>
                    </td>
                    <td style="padding: 16px; font-size: 14px;">
                      ❌ No
                    </td>
                    <td style="padding: 16px; font-size: 14px;">
                      <span style="background-color: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                        Active
                      </span>
                    </td>
                    <td style="padding: 16px; text-align: right;">
                      <button class="edit-feature-btn" data-id="id_1766402307416_sv0vkrmgu" style="color: #3b82f6; background: none; border: none; cursor: pointer; margin-right: 12px; font-size: 14px; font-weight: 600;">Edit</button>
                      <button class="delete-feature-btn" data-id="id_1766402307416_sv0vkrmgu" style="color: #dc2626; background: none; border: none; cursor: pointer; font-size: 14px; font-weight: 600;">Delete</button>
                    </td>
                  </tr>
                
                  <tr style="border-top: 1px solid #e2e8f0;">
                    <td style="padding: 16px; font-size: 16px; color: #1e293b; font-weight: 600;">Custom Domain</td>
                    <td style="padding: 16px; font-size: 14px; color: #64748b; font-family: monospace;">custom_domain</td>
                    <td style="padding: 16px; font-size: 14px;">
                      <span style="background-color: #e0e7ff; color: #4338ca; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                        Boolean
                      </span>
                    </td>
                    <td style="padding: 16px; font-size: 14px;">
                      ❌ No
                    </td>
                    <td style="padding: 16px; font-size: 14px;">
                      <span style="background-color: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                        Active
                      </span>
                    </td>
                    <td style="padding: 16px; text-align: right;">
                      <button class="edit-feature-btn" data-id="id_1766402307416_nrkhzfmx1" style="color: #3b82f6; background: none; border: none; cursor: pointer; margin-right: 12px; font-size: 14px; font-weight: 600;">Edit</button>
                      <button class="delete-feature-btn" data-id="id_1766402307416_nrkhzfmx1" style="color: #dc2626; background: none; border: none; cursor: pointer; font-size: 14px; font-weight: 600;">Delete</button>
                    </td>
                  </tr>
                
                  <tr style="border-top: 1px solid #e2e8f0;">
                    <td style="padding: 16px; font-size: 16px; color: #1e293b; font-weight: 600;">Priority Support</td>
                    <td style="padding: 16px; font-size: 14px; color: #64748b; font-family: monospace;">priority_support</td>
                    <td style="padding: 16px; font-size: 14px;">
                      <span style="background-color: #e0e7ff; color: #4338ca; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                        Boolean
                      </span>
                    </td>
                    <td style="padding: 16px; font-size: 14px;">
                      ❌ No
                    </td>
                    <td style="padding: 16px; font-size: 14px;">
                      <span style="background-color: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                        Active
                      </span>
                    </td>
                    <td style="padding: 16px; text-align: right;">
                      <button class="edit-feature-btn" data-id="id_1766402307416_isgakdv0a" style="color: #3b82f6; background: none; border: none; cursor: pointer; margin-right: 12px; font-size: 14px; font-weight: 600;">Edit</button>
                      <button class="delete-feature-btn" data-id="id_1766402307416_isgakdv0a" style="color: #dc2626; background: none; border: none; cursor: pointer; font-size: 14px; font-weight: 600;">Delete</button>
                    </td>
                  </tr>
                
                  <tr style="border-top: 1px solid #e2e8f0;">
                    <td style="padding: 16px; font-size: 16px; color: #1e293b; font-weight: 600;">API Calls (Monthly)</td>
                    <td style="padding: 16px; font-size: 14px; color: #64748b; font-family: monospace;">api_calls_monthly</td>
                    <td style="padding: 16px; font-size: 14px;">
                      <span style="background-color: #e0e7ff; color: #4338ca; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                        Number
                      </span>
                    </td>
                    <td style="padding: 16px; font-size: 14px;">
                      ✅ Yes
                    </td>
                    <td style="padding: 16px; font-size: 14px;">
                      <span style="background-color: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                        Active
                      </span>
                    </td>
                    <td style="padding: 16px; text-align: right;">
                      <button class="edit-feature-btn" data-id="id_1766402307416_qjqic8bvk" style="color: #3b82f6; background: none; border: none; cursor: pointer; margin-right: 12px; font-size: 14px; font-weight: 600;">Edit</button>
                      <button class="delete-feature-btn" data-id="id_1766402307416_qjqic8bvk" style="color: #dc2626; background: none; border: none; cursor: pointer; font-size: 14px; font-weight: 600;">Delete</button>
                    </td>
                  </tr>
                
              </tbody>
            </table>
          </div>
        </div>
      </div>
          </div>
        </div>

        <!-- Modal Container -->
        <div id="modal-container"></div>
      </div>
  <script>
    const config = {
      background_color: "#f8fafc",
      surface_color: "#ffffff",
      text_color: "#1e293b",
      primary_action_color: "#3b82f6",
      secondary_action_color: "#64748b",
      panel_title: "Subscription Management",
      welcome_message: "Manage your subscription features and packages",
      font_family: "Inter",
      font_size: 16
    };

    let currentView = 'features';
    let showModal = false;
    let modalType = '';
    let editingItem = null;
    let selectedPackage = null;
    let selectedCurrency = 'USD';
    
    // Local storage keys
    const STORAGE_KEYS = {
      features: 'subscription_features',
      packages: 'subscription_packages',
      featureConfigs: 'subscription_feature_configs',
      pricing: 'subscription_pricing'
    };

    // Initialize data from localStorage or defaults
    let features = JSON.parse(localStorage.getItem(STORAGE_KEYS.features) || '[]');
    let packages = JSON.parse(localStorage.getItem(STORAGE_KEYS.packages) || '[]');
    let featureConfigs = JSON.parse(localStorage.getItem(STORAGE_KEYS.featureConfigs) || '[]');
    let pricing = JSON.parse(localStorage.getItem(STORAGE_KEYS.pricing) || '[]');

    const currencies = ['USD', 'EUR', 'GBP', 'INR', 'AUD'];
    const billingCycles = ['Monthly', 'Annual'];

    // Helper functions for data persistence
    function saveToLocalStorage() {
      localStorage.setItem(STORAGE_KEYS.features, JSON.stringify(features));
      localStorage.setItem(STORAGE_KEYS.packages, JSON.stringify(packages));
      localStorage.setItem(STORAGE_KEYS.featureConfigs, JSON.stringify(featureConfigs));
      localStorage.setItem(STORAGE_KEYS.pricing, JSON.stringify(pricing));
    }

    function generateId() {
      return 'id_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    }

    // Seed initial data if empty
    function seedInitialData() {
      if (features.length === 0 && packages.length === 0) {
        features = [
          { id: generateId(), name: 'Projects', key: 'projects', dataType: 'Number', isMetered: false, description: 'Number of projects allowed', status: 'Active', createdAt: new Date().toISOString() },
          { id: generateId(), name: 'Storage (GB)', key: 'storage_gb', dataType: 'Number', isMetered: true, description: 'Storage space in gigabytes', status: 'Active', createdAt: new Date().toISOString() },
          { id: generateId(), name: 'Team Members', key: 'team_members', dataType: 'Number', isMetered: false, description: 'Number of team members allowed', status: 'Active', createdAt: new Date().toISOString() },
          { id: generateId(), name: 'API Access', key: 'api_access', dataType: 'Boolean', isMetered: false, description: 'Enable API access', status: 'Active', createdAt: new Date().toISOString() },
          { id: generateId(), name: 'Custom Domain', key: 'custom_domain', dataType: 'Boolean', isMetered: false, description: 'Allow custom domain setup', status: 'Active', createdAt: new Date().toISOString() },
          { id: generateId(), name: 'Priority Support', key: 'priority_support', dataType: 'Boolean', isMetered: false, description: 'Access to priority support', status: 'Active', createdAt: new Date().toISOString() },
          { id: generateId(), name: 'API Calls (Monthly)', key: 'api_calls_monthly', dataType: 'Number', isMetered: true, description: 'Monthly API call limit', status: 'Active', createdAt: new Date().toISOString() }
        ];

        packages = [
          { id: generateId(), packageName: 'Basic Plan', packageCode: 'BASIC', description: 'Perfect for individuals and small teams getting started', billingType: 'Monthly, Annual', trialEnabled: true, trialDays: 14, creditCardRequired: false, status: 'Active', sortOrder: 0, createdAt: new Date().toISOString() },
          { id: generateId(), packageName: 'Professional Plan', packageCode: 'PRO', description: 'For growing teams and businesses needing more power', billingType: 'Monthly, Annual', trialEnabled: true, trialDays: 30, creditCardRequired: true, status: 'Active', sortOrder: 1, createdAt: new Date().toISOString() },
          { id: generateId(), packageName: 'Enterprise Plan', packageCode: 'ENTERPRISE', description: 'Advanced features and unlimited resources for large organizations', billingType: 'Monthly, Annual', trialEnabled: false, trialDays: 0, creditCardRequired: true, status: 'Active', sortOrder: 2, createdAt: new Date().toISOString() }
        ];

        saveToLocalStorage();
      }
    }

    function renderApp() {
      const baseFont = config.font_size;
      
      const app = document.getElementById('app');
      app.style.backgroundColor = config.background_color;
      app.style.color = config.text_color;
      app.style.fontFamily = `${config.font_family}, system-ui, -apple-system, sans-serif`;

      app.innerHTML = `
        <div class="min-h-full" style="background-color: ${config.background_color};">
          <!-- Header -->
          <div class="shadow-sm" style="background-color: ${config.surface_color};">
            <div class="max-w-7xl mx-auto px-6 py-6">
              <div class="flex items-center justify-between">
                <div>
                  <h1 style="font-size: ${baseFont * 1.75}px; color: ${config.text_color}; font-weight: 700; margin: 0;">
                    ${config.panel_title}
                  </h1>
                  <p style="font-size: ${baseFont * 0.875}px; color: ${config.secondary_action_color}; margin-top: 4px;">
                    ${config.welcome_message}
                  </p>
                </div>
                <div style="display: flex; align-items: center; gap: 16px;">
                  <button id="copy-code-btn" style="background-color: #10b981; color: white; padding: 10px 20px; border-radius: 8px; font-size: ${baseFont * 0.875}px; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    📋 Copy HTML Code
                  </button>
                  <button id="help-btn" style="background-color: ${config.secondary_action_color}; color: white; padding: 10px 20px; border-radius: 8px; font-size: ${baseFont * 0.875}px; font-weight: 600; border: none; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    📖 Help & Guide
                  </button>
                  <div style="background-color: ${config.primary_action_color}; color: white; padding: 8px 16px; border-radius: 50%; font-size: ${baseFont * 1.25}px; font-weight: 600;">
                    SA
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Navigation Tabs -->
          <div class="max-w-7xl mx-auto px-6 mt-6">
            <div class="flex gap-8 border-b" style="border-color: #e2e8f0;">
              ${renderTab('features', '📋 Features', baseFont)}
              ${renderTab('packages', '📦 Packages', baseFont)}
              ${renderTab('configuration', '⚙�� Configuration', baseFont)}
              ${renderTab('pricing', '��� Pricing', baseFont)}
            </div>
          </div>

          <!-- Main Content -->
          <div class="max-w-7xl mx-auto px-6 py-8">
            <div id="content-area"></div>
          </div>
        </div>

        <!-- Modal Container -->
        <div id="modal-container"></div>
      `;

      setupTabListeners();
      setupHelpListener();
      setupCopyCodeListener();
      renderContent();
      if (showModal) {
        renderModal();
      }
    }

    function renderTab(view, label, baseFont) {
      const isActive = currentView === view;
      return `
        <button 
          class="tab-button px-4 py-3 ${isActive ? 'tab-active' : ''}" 
          data-view="${view}"
          style="font-size: ${baseFont}px; color: ${isActive ? config.primary_action_color : config.secondary_action_color}; font-weight: ${isActive ? '600' : '500'}; border-color: ${config.primary_action_color}; background: none; border: none; cursor: pointer; border-bottom-width: ${isActive ? '3px' : '0'};">
          ${label}
        </button>
      `;
    }

    function setupTabListeners() {
      document.querySelectorAll('.tab-button').forEach(btn => {
        btn.addEventListener('click', (e) => {
          currentView = e.target.dataset.view;
          renderApp();
        });
      });
    }

    function setupHelpListener() {
      const helpBtn = document.getElementById('help-btn');
      if (helpBtn) {
        helpBtn.addEventListener('click', () => {
          showModal = true;
          modalType = 'help';
          renderApp();
        });
      }
    }

    function setupCopyCodeListener() {
      const copyBtn = document.getElementById('copy-code-btn');
      if (copyBtn) {
        copyBtn.addEventListener('click', async () => {
          const htmlCode = document.documentElement.outerHTML;
          
          try {
            // Try modern clipboard API first
            if (navigator.clipboard && navigator.clipboard.writeText) {
              await navigator.clipboard.writeText(htmlCode);
            } else {
              // Fallback method for older browsers or restricted contexts
              const textArea = document.createElement('textarea');
              textArea.value = htmlCode;
              textArea.style.position = 'fixed';
              textArea.style.left = '-999999px';
              textArea.style.top = '-999999px';
              document.body.appendChild(textArea);
              textArea.focus();
              textArea.select();
              
              const successful = document.execCommand('copy');
              document.body.removeChild(textArea);
              
              if (!successful) {
                throw new Error('Copy command failed');
              }
            }
            
            const originalText = copyBtn.innerHTML;
            copyBtn.innerHTML = '✅ Copied!';
            copyBtn.style.backgroundColor = '#059669';
            
            setTimeout(() => {
              copyBtn.innerHTML = originalText;
              copyBtn.style.backgroundColor = '#10b981';
            }, 2000);
            
            showToast('HTML code copied to clipboard!', 'success');
          } catch (err) {
            console.error('Copy failed:', err);
            
            // Show manual copy instructions
            showModal = true;
            modalType = 'copy-error';
            renderApp();
          }
        });
      }
    }

    function renderContent() {
      const contentArea = document.getElementById('content-area');
      
      switch(currentView) {
        case 'features':
          contentArea.innerHTML = renderFeaturesView();
          setupFeaturesListeners();
          break;
        case 'packages':
          contentArea.innerHTML = renderPackagesView();
          setupPackagesListeners();
          break;
        case 'configuration':
          contentArea.innerHTML = renderConfigurationView();
          setupConfigurationListeners();
          break;
        case 'pricing':
          contentArea.innerHTML = renderPricingView();
          setupPricingListeners();
          break;
      }
    }

    function renderFeaturesView() {
      const baseFont = config.font_size;

      return `
        <div>
          <div class="flex justify-between items-center mb-6">
            <div>
              <h2 style="font-size: ${baseFont * 1.5}px; color: ${config.text_color}; font-weight: 700; margin: 0 0 8px 0;">
                Subscription Features
              </h2>
              <p style="font-size: ${baseFont * 0.875}px; color: ${config.secondary_action_color}; margin: 0;">
                Define features that will be available in your subscription packages
              </p>
            </div>
            <button id="add-feature-btn" style="background-color: ${config.primary_action_color}; color: white; padding: 12px 24px; border-radius: 8px; font-size: ${baseFont}px; font-weight: 600; border: none; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
              + Add Feature
            </button>
          </div>

          <!-- Info Box -->
          <div style="background-color: #eff6ff; border-left: 4px solid ${config.primary_action_color}; padding: 16px 20px; border-radius: 8px; margin-bottom: 24px;">
            <div style="display: flex; gap: 12px;">
              <div style="font-size: ${baseFont * 1.25}px;">ℹ️</div>
              <div>
                <h4 style="font-size: ${baseFont}px; font-weight: 600; margin: 0 0 8px 0; color: ${config.text_color};">Quick Tips</h4>
                <ul style="margin: 0; padding-left: 20px; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; line-height: 1.8;">
                  <li><strong>Feature Key</strong> is auto-generated and used in your system code</li>
                  <li>Use <strong>Number</strong> type for countable limits (projects, users, storage GB)</li>
                  <li>Use <strong>Boolean</strong> for on/off features (API access, custom domain)</li>
                  <li>Enable <strong>Metered</strong> only if you track usage for billing purposes</li>
                  <li>Set to <strong>Inactive</strong> to temporarily hide without deleting</li>
                </ul>
              </div>
            </div>
          </div>

          <div style="background-color: ${config.surface_color}; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
            <table style="width: 100%; border-collapse: collapse;">
              <thead style="background-color: ${config.background_color};">
                <tr>
                  <th style="padding: 16px; text-align: left; font-size: ${baseFont * 0.875}px; font-weight: 600; color: ${config.secondary_action_color};">Feature Name</th>
                  <th style="padding: 16px; text-align: left; font-size: ${baseFont * 0.875}px; font-weight: 600; color: ${config.secondary_action_color};">Feature Key</th>
                  <th style="padding: 16px; text-align: left; font-size: ${baseFont * 0.875}px; font-weight: 600; color: ${config.secondary_action_color};">Data Type</th>
                  <th style="padding: 16px; text-align: left; font-size: ${baseFont * 0.875}px; font-weight: 600; color: ${config.secondary_action_color};">Metered</th>
                  <th style="padding: 16px; text-align: left; font-size: ${baseFont * 0.875}px; font-weight: 600; color: ${config.secondary_action_color};">Status</th>
                  <th style="padding: 16px; text-align: right; font-size: ${baseFont * 0.875}px; font-weight: 600; color: ${config.secondary_action_color};">Actions</th>
                </tr>
              </thead>
              <tbody>
                ${features.length === 0 ? `
                  <tr>
                    <td colspan="6" style="padding: 48px; text-align: center; font-size: ${baseFont}px; color: ${config.secondary_action_color};">
                      No features yet. Click "+ Add Feature" to create one.
                    </td>
                  </tr>
                ` : features.map((feature, index) => `
                  <tr style="border-top: 1px solid #e2e8f0;">
                    <td style="padding: 16px; font-size: ${baseFont}px; color: ${config.text_color}; font-weight: 600;">${feature.name}</td>
                    <td style="padding: 16px; font-size: ${baseFont * 0.875}px; color: ${config.secondary_action_color}; font-family: monospace;">${feature.key}</td>
                    <td style="padding: 16px; font-size: ${baseFont * 0.875}px;">
                      <span style="background-color: #e0e7ff; color: #4338ca; padding: 4px 12px; border-radius: 12px; font-size: ${baseFont * 0.75}px; font-weight: 600;">
                        ${feature.dataType}
                      </span>
                    </td>
                    <td style="padding: 16px; font-size: ${baseFont * 0.875}px;">
                      ${feature.isMetered ? '✅ Yes' : '❌ No'}
                    </td>
                    <td style="padding: 16px; font-size: ${baseFont * 0.875}px;">
                      <span style="background-color: ${feature.status === 'Active' ? '#dcfce7' : '#fee2e2'}; color: ${feature.status === 'Active' ? '#166534' : '#991b1b'}; padding: 4px 12px; border-radius: 12px; font-size: ${baseFont * 0.75}px; font-weight: 600;">
                        ${feature.status}
                      </span>
                    </td>
                    <td style="padding: 16px; text-align: right;">
                      <button class="edit-feature-btn" data-id="${feature.id}" style="color: ${config.primary_action_color}; background: none; border: none; cursor: pointer; margin-right: 12px; font-size: ${baseFont * 0.875}px; font-weight: 600;">Edit</button>
                      <button class="delete-feature-btn" data-id="${feature.id}" style="color: #dc2626; background: none; border: none; cursor: pointer; font-size: ${baseFont * 0.875}px; font-weight: 600;">Delete</button>
                    </td>
                  </tr>
                `).join('')}
              </tbody>
            </table>
          </div>
        </div>
      `;
    }

    function renderPackagesView() {
      const baseFont = config.font_size;

      return `
        <div>
          <div class="flex justify-between items-center mb-6">
            <div>
              <h2 style="font-size: ${baseFont * 1.5}px; color: ${config.text_color}; font-weight: 700; margin: 0 0 8px 0;">
                Subscription Packages
              </h2>
              <p style="font-size: ${baseFont * 0.875}px; color: ${config.secondary_action_color}; margin: 0;">
                Create subscription tiers that customers can purchase (Basic, Pro, Enterprise)
              </p>
            </div>
            <button id="add-package-btn" style="background-color: ${config.primary_action_color}; color: white; padding: 12px 24px; border-radius: 8px; font-size: ${baseFont}px; font-weight: 600; border: none; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
              + Create Package
            </button>
          </div>

          <!-- Info Box -->
          <div style="background-color: #eff6ff; border-left: 4px solid ${config.primary_action_color}; padding: 16px 20px; border-radius: 8px; margin-bottom: 24px;">
            <div style="display: flex; gap: 12px;">
              <div style="font-size: ${baseFont * 1.25}px;">💡</div>
              <div>
                <h4 style="font-size: ${baseFont}px; font-weight: 600; margin: 0 0 8px 0; color: ${config.text_color};">Best Practices</h4>
                <ul style="margin: 0; padding-left: 20px; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; line-height: 1.8;">
                  <li>Create 3-4 packages maximum to avoid overwhelming customers</li>
                  <li>Common pattern: <strong>Free/Basic → Pro → Enterprise</strong></li>
                  <li>Enable both <strong>Monthly and Annual</strong> billing for flexibility</li>
                  <li>Use <strong>Trial Period</strong> to let customers test premium features (7-30 days typical)</li>
                  <li><strong>Credit Card Required</strong> determines if payment info is needed during signup</li>
                  <li>After creating packages, go to <strong>Configuration</strong> to set feature limits</li>
                </ul>
              </div>
            </div>
          </div>

          ${packages.length === 0 ? `
            <div style="background-color: ${config.surface_color}; border-radius: 12px; padding: 48px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
              <p style="font-size: ${baseFont}px; color: ${config.secondary_action_color}; margin: 0;">
                No packages yet. Click "+ Create Package" to get started.
              </p>
            </div>
          ` : `
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 24px;">
              ${packages.map((pkg, index) => `
                <div style="background-color: ${config.surface_color}; border-radius: 12px; padding: 24px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                  <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 16px;">
                    <div>
                      <h3 style="font-size: ${baseFont * 1.25}px; color: ${config.text_color}; font-weight: 700; margin: 0 0 8px 0;">
                        ${pkg.packageName}
                      </h3>
                      <p style="font-size: ${baseFont * 0.875}px; color: ${config.secondary_action_color}; font-family: monospace; margin: 0;">
                        ${pkg.packageCode}
                      </p>
                    </div>
                    <span style="background-color: ${pkg.status === 'Active' ? '#dcfce7' : '#fee2e2'}; color: ${pkg.status === 'Active' ? '#166534' : '#991b1b'}; padding: 4px 12px; border-radius: 12px; font-size: ${baseFont * 0.75}px; font-weight: 600;">
                      ${pkg.status}
                    </span>
                  </div>
                  
                  <p style="font-size: ${baseFont * 0.875}px; color: ${config.text_color}; margin: 0 0 16px 0; line-height: 1.5;">
                    ${pkg.description || 'No description'}
                  </p>

                  <div style="display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap;">
                    ${pkg.billingType.split(',').map(type => `
                      <span style="background-color: #dbeafe; color: #1e40af; padding: 4px 12px; border-radius: 12px; font-size: ${baseFont * 0.75}px; font-weight: 600;">
                        ${type.trim()}
                      </span>
                    `).join('')}
                  </div>

                  ${pkg.trialEnabled ? `
                    <p style="font-size: ${baseFont * 0.875}px; color: ${config.secondary_action_color}; margin: 0 0 8px 0;">
                      🎁 ${pkg.trialDays} day trial
                    </p>
                  ` : ''}
                  
                  <p style="font-size: ${baseFont * 0.875}px; color: ${config.secondary_action_color}; margin: 0 0 16px 0;">
                    💳 Credit Card: ${pkg.creditCardRequired ? 'Required' : 'Not Required'}
                  </p>

                  <div style="display: flex; gap: 8px;">
                    <button class="edit-package-btn" data-id="${pkg.id}" style="flex: 1; background-color: ${config.primary_action_color}; color: white; padding: 10px; border-radius: 6px; font-size: ${baseFont * 0.875}px; font-weight: 600; border: none; cursor: pointer;">
                      Edit
                    </button>
                    <button class="delete-package-btn" data-id="${pkg.id}" style="background-color: #fee2e2; color: #991b1b; padding: 10px 16px; border-radius: 6px; font-size: ${baseFont * 0.875}px; font-weight: 600; border: none; cursor: pointer;">
                      Delete
                    </button>
                  </div>
                </div>
              `).join('')}
            </div>
          `}
        </div>
      `;
    }

    function renderConfigurationView() {
      const baseFont = config.font_size;

      return `
        <div>
          <div class="mb-6">
            <div style="margin-bottom: 16px;">
              <h2 style="font-size: ${baseFont * 1.5}px; color: ${config.text_color}; font-weight: 700; margin: 0 0 8px 0;">
                Package Feature Configuration
              </h2>
              <p style="font-size: ${baseFont * 0.875}px; color: ${config.secondary_action_color}; margin: 0;">
                Define feature availability and limits for each subscription package
              </p>
            </div>

            <!-- Info Box -->
            <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px 20px; border-radius: 8px; margin-bottom: 20px;">
              <div style="display: flex; gap: 12px;">
                <div style="font-size: ${baseFont * 1.25}px;">⚙️</div>
                <div>
                  <h4 style="font-size: ${baseFont}px; font-weight: 600; margin: 0 0 8px 0; color: ${config.text_color};">Understanding Limit Types</h4>
                  <div style="font-size: ${baseFont * 0.875}px; color: ${config.text_color}; line-height: 1.8;">
                    <p style="margin: 0 0 8px 0;"><strong>• Limited:</strong> Set specific number (e.g., 5 projects, 10GB storage, 1000 API calls)</p>
                    <p style="margin: 0 0 8px 0;"><strong>• Unlimited:</strong> No restrictions - typically used for premium packages</p>
                    <p style="margin: 0;"><strong>• Disabled:</strong> Feature not available in this package</p>
                  </div>
                </div>
              </div>
            </div>
            
            <div style="max-width: 400px;">
              <label style="display: block; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; font-weight: 600; margin-bottom: 8px;">
                Select Package
              </label>
              <select id="package-selector" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont}px; background-color: ${config.surface_color}; color: ${config.text_color};">
                <option value="">-- Choose a package --</option>
                ${packages.map(pkg => `
                  <option value="${pkg.id}" ${selectedPackage === pkg.id ? 'selected' : ''}>
                    ${pkg.packageName}
                  </option>
                `).join('')}
              </select>
            </div>
          </div>

          ${!selectedPackage ? `
            <div style="background-color: ${config.surface_color}; border-radius: 12px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
              <p style="font-size: ${baseFont}px; color: ${config.secondary_action_color}; margin: 0 0 24px 0; text-align: center;">
                Select a package to configure its features
              </p>
              
              <!-- Example Table -->
              <div style="background-color: ${config.background_color}; padding: 20px; border-radius: 8px; border: 2px dashed #cbd5e1;">
                <h4 style="font-size: ${baseFont}px; font-weight: 600; margin: 0 0 16px 0; color: ${config.text_color}; text-align: center;">
                  📊 Example Configuration
                </h4>
                <table style="width: 100%; border-collapse: collapse; font-size: ${baseFont * 0.875}px;">
                  <thead>
                    <tr style="background-color: ${config.surface_color};">
                      <th style="padding: 10px; text-align: left; border: 1px solid #e2e8f0; font-weight: 600;">Feature</th>
                      <th style="padding: 10px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600;">Basic Plan</th>
                      <th style="padding: 10px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600;">Pro Plan</th>
                      <th style="padding: 10px; text-align: center; border: 1px solid #e2e8f0; font-weight: 600;">Enterprise</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td style="padding: 10px; border: 1px solid #e2e8f0; color: ${config.text_color};">Projects</td>
                      <td style="padding: 10px; border: 1px solid #e2e8f0; text-align: center; color: ${config.text_color};">Limited (5)</td>
                      <td style="padding: 10px; border: 1px solid #e2e8f0; text-align: center; color: ${config.text_color};">Limited (50)</td>
                      <td style="padding: 10px; border: 1px solid #e2e8f0; text-align: center; color: ${config.text_color};">Unlimited</td>
                    </tr>
                    <tr>
                      <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: ${config.surface_color}; color: ${config.text_color};">Storage (GB)</td>
                      <td style="padding: 10px; border: 1px solid #e2e8f0; text-align: center; background-color: ${config.surface_color}; color: ${config.text_color};">Limited (10)</td>
                      <td style="padding: 10px; border: 1px solid #e2e8f0; text-align: center; background-color: ${config.surface_color}; color: ${config.text_color};">Limited (100)</td>
                      <td style="padding: 10px; border: 1px solid #e2e8f0; text-align: center; background-color: ${config.surface_color}; color: ${config.text_color};">Unlimited</td>
                    </tr>
                    <tr>
                      <td style="padding: 10px; border: 1px solid #e2e8f0; color: ${config.text_color};">Team Members</td>
                      <td style="padding: 10px; border: 1px solid #e2e8f0; text-align: center; color: ${config.text_color};">Limited (3)</td>
                      <td style="padding: 10px; border: 1px solid #e2e8f0; text-align: center; color: ${config.text_color};">Limited (20)</td>
                      <td style="padding: 10px; border: 1px solid #e2e8f0; text-align: center; color: ${config.text_color};">Unlimited</td>
                    </tr>
                    <tr>
                      <td style="padding: 10px; border: 1px solid #e2e8f0; background-color: ${config.surface_color}; color: ${config.text_color};">API Access</td>
                      <td style="padding: 10px; border: 1px solid #e2e8f0; text-align: center; background-color: ${config.surface_color}; color: #dc2626;">Disabled</td>
                      <td style="padding: 10px; border: 1px solid #e2e8f0; text-align: center; background-color: ${config.surface_color}; color: ${config.text_color};">Limited (1000)</td>
                      <td style="padding: 10px; border: 1px solid #e2e8f0; text-align: center; background-color: ${config.surface_color}; color: ${config.text_color};">Unlimited</td>
                    </tr>
                  </tbody>
                </table>
                <p style="font-size: ${baseFont * 0.75}px; color: ${config.secondary_action_color}; margin: 12px 0 0 0; text-align: center; font-style: italic;">
                  This is just an example. Your actual features will appear once you select a package.
                </p>
              </div>
            </div>
          ` : `
            <div style="background-color: ${config.surface_color}; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
              <table style="width: 100%; border-collapse: collapse;">
                <thead style="background-color: ${config.background_color};">
                  <tr>
                    <th style="padding: 16px; text-align: left; font-size: ${baseFont * 0.875}px; font-weight: 600; color: ${config.secondary_action_color};">Feature Name</th>
                    <th style="padding: 16px; text-align: left; font-size: ${baseFont * 0.875}px; font-weight: 600; color: ${config.secondary_action_color};">Limit Type</th>
                    <th style="padding: 16px; text-align: left; font-size: ${baseFont * 0.875}px; font-weight: 600; color: ${config.secondary_action_color};">Limit Value</th>
                  </tr>
                </thead>
                <tbody>
                  ${features.map((feature, index) => {
                    const existingConfig = featureConfigs.find(fc => 
                      fc.packageId === selectedPackage && fc.featureId === feature.id
                    );
                    const limitType = existingConfig?.limitType || 'Limited';
                    const limitValue = existingConfig?.limitValue || 0;
                    
                    return `
                      <tr style="border-top: 1px solid #e2e8f0;">
                        <td style="padding: 16px; font-size: ${baseFont}px; color: ${config.text_color}; font-weight: 600;">
                          ${feature.name}
                        </td>
                        <td style="padding: 16px;">
                          <div style="display: flex; gap: 16px;">
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: ${baseFont * 0.875}px;">
                              <input type="radio" name="limit-${feature.id}" value="Limited" ${limitType === 'Limited' ? 'checked' : ''} class="feature-limit-type" data-feature="${feature.id}">
                              <span>Limited</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: ${baseFont * 0.875}px;">
                              <input type="radio" name="limit-${feature.id}" value="Unlimited" ${limitType === 'Unlimited' ? 'checked' : ''} class="feature-limit-type" data-feature="${feature.id}">
                              <span>Unlimited</span>
                            </label>
                            <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; font-size: ${baseFont * 0.875}px;">
                              <input type="radio" name="limit-${feature.id}" value="Disabled" ${limitType === 'Disabled' ? 'checked' : ''} class="feature-limit-type" data-feature="${feature.id}">
                              <span>Disabled</span>
                            </label>
                          </div>
                        </td>
                        <td style="padding: 16px;">
                          <input 
                            type="number" 
                            class="feature-limit-value" 
                            data-feature="${feature.id}"
                            value="${limitValue}"
                            ${limitType !== 'Limited' ? 'disabled' : ''}
                            style="width: 120px; padding: 8px 12px; border: 2px solid #e2e8f0; border-radius: 6px; font-size: ${baseFont * 0.875}px; ${limitType !== 'Limited' ? 'opacity: 0.5; cursor: not-allowed;' : ''}"
                            min="0"
                          >
                        </td>
                      </tr>
                    `;
                  }).join('')}
                </tbody>
              </table>
            </div>

            <div style="margin-top: 24px; display: flex; justify-content: flex-end;">
              <button id="save-config-btn" style="background-color: ${config.primary_action_color}; color: white; padding: 12px 32px; border-radius: 8px; font-size: ${baseFont}px; font-weight: 600; border: none; cursor: pointer; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                Save Changes
              </button>
            </div>
          `}
        </div>
      `;
    }

    function renderPricingView() {
      const baseFont = config.font_size;

      const selectedPkg = packages.find(p => p.id === selectedPackage);
      const availableCycles = selectedPkg ? selectedPkg.billingType.split(',').map(t => t.trim()) : [];
      
      const existingPricing = pricing.filter(p => p.packageId === selectedPackage && p.currency === selectedCurrency);
      const existingCycles = existingPricing.map(p => p.billingCycle);
      
      const missingCycles = availableCycles.filter(cycle => !existingCycles.includes(cycle));
      const canAddPrice = selectedPackage && missingCycles.length > 0;

      return `
        <div>
          <div class="mb-6">
            <div style="margin-bottom: 16px;">
              <h2 style="font-size: ${baseFont * 1.5}px; color: ${config.text_color}; font-weight: 700; margin: 0 0 8px 0;">
                Package Pricing
              </h2>
              <p style="font-size: ${baseFont * 0.875}px; color: ${config.secondary_action_color}; margin: 0;">
                Set pricing for packages across different currencies and billing cycles
              </p>
            </div>

            <!-- Info Box -->
            <div style="background-color: #dcfce7; border-left: 4px solid #10b981; padding: 16px 20px; border-radius: 8px; margin-bottom: 20px;">
              <div style="display: flex; gap: 12px;">
                <div style="font-size: ${baseFont * 1.25}px;">💰</div>
                <div>
                  <h4 style="font-size: ${baseFont}px; font-weight: 600; margin: 0 0 8px 0; color: ${config.text_color};">Pricing Strategies</h4>
                  <ul style="margin: 0; padding-left: 20px; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; line-height: 1.8;">
                    <li>Offer <strong>15-20% discount</strong> on annual plans to encourage yearly commitment</li>
                    <li>Use <strong>psychological pricing</strong> ($29 instead of $30, $99 instead of $100)</li>
                    <li>Set <strong>$0.00</strong> pricing for free tier packages</li>
                    <li>Add separate prices for <strong>each currency</strong> you want to support</li>
                    <li>Each package can have <strong>one price per billing cycle per currency</strong> (no duplicates)</li>
                  </ul>
                </div>
              </div>
            </div>
            
            <div style="display: flex; gap: 16px; max-width: 800px;">
              <div style="flex: 1;">
                <label style="display: block; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; font-weight: 600; margin-bottom: 8px;">
                  Select Package
                </label>
                <select id="pricing-package-selector" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont}px; background-color: ${config.surface_color}; color: ${config.text_color};">
                  <option value="">-- Choose a package --</option>
                  ${packages.map(pkg => `
                    <option value="${pkg.id}" ${selectedPackage === pkg.id ? 'selected' : ''}>
                      ${pkg.packageName}
                    </option>
                  `).join('')}
                </select>
              </div>
              
              <div style="flex: 1;">
                <label style="display: block; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; font-weight: 600; margin-bottom: 8px;">
                  Currency
                </label>
                <select id="currency-selector" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont}px; background-color: ${config.surface_color}; color: ${config.text_color};">
                  ${currencies.map(curr => `
                    <option value="${curr}" ${selectedCurrency === curr ? 'selected' : ''}>${curr}</option>
                  `).join('')}
                </select>
              </div>
            </div>
          </div>

          ${!selectedPackage ? `
            <div style="background-color: ${config.surface_color}; border-radius: 12px; padding: 48px; text-align: center; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
              <p style="font-size: ${baseFont}px; color: ${config.secondary_action_color}; margin: 0;">
                Select a package to manage pricing
              </p>
            </div>
          ` : `
            <div>
              <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="font-size: ${baseFont * 1.125}px; color: ${config.text_color}; font-weight: 600; margin: 0;">
                  Pricing for ${selectedCurrency}
                </h3>
                <button id="add-price-btn" ${!canAddPrice ? 'disabled' : ''} style="background-color: ${canAddPrice ? config.primary_action_color : '#cbd5e1'}; color: white; padding: 10px 20px; border-radius: 8px; font-size: ${baseFont * 0.875}px; font-weight: 600; border: none; cursor: ${canAddPrice ? 'pointer' : 'not-allowed'}; opacity: ${canAddPrice ? '1' : '0.6'};">
                  + Add Price
                </button>
              </div>

              ${!canAddPrice && existingPricing.length > 0 ? `
                <div style="background-color: #eff6ff; border-left: 4px solid ${config.primary_action_color}; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;">
                  <p style="font-size: ${baseFont * 0.875}px; color: ${config.text_color}; margin: 0;">
                    ℹ️ All billing cycles for this package and currency have been configured. Edit or delete existing prices to make changes.
                  </p>
                </div>
              ` : ''}

              <div style="background-color: ${config.surface_color}; border-radius: 12px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); overflow: hidden;">
                <table style="width: 100%; border-collapse: collapse;">
                  <thead style="background-color: ${config.background_color};">
                    <tr>
                      <th style="padding: 16px; text-align: left; font-size: ${baseFont * 0.875}px; font-weight: 600; color: ${config.secondary_action_color};">Billing Cycle</th>
                      <th style="padding: 16px; text-align: left; font-size: ${baseFont * 0.875}px; font-weight: 600; color: ${config.secondary_action_color};">Price</th>
                      <th style="padding: 16px; text-align: left; font-size: ${baseFont * 0.875}px; font-weight: 600; color: ${config.secondary_action_color};">Status</th>
                      <th style="padding: 16px; text-align: right; font-size: ${baseFont * 0.875}px; font-weight: 600; color: ${config.secondary_action_color};">Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    ${existingPricing.length === 0 ? `
                      <tr>
                        <td colspan="4" style="padding: 48px; text-align: center; font-size: ${baseFont}px; color: ${config.secondary_action_color};">
                          No pricing set for this package and currency. Click "Add Price" to get started.
                        </td>
                      </tr>
                    ` : existingPricing.map((price, index) => `
                      <tr style="border-top: 1px solid #e2e8f0;">
                        <td style="padding: 16px; font-size: ${baseFont}px; color: ${config.text_color}; font-weight: 600;">
                          ${price.billingCycle}
                        </td>
                        <td style="padding: 16px; font-size: ${baseFont * 1.125}px; color: ${config.text_color}; font-weight: 700;">
                          ${price.currency} ${price.price.toFixed(2)}
                        </td>
                        <td style="padding: 16px; font-size: ${baseFont * 0.875}px;">
                          <span style="background-color: ${price.status === 'Active' ? '#dcfce7' : '#fee2e2'}; color: ${price.status === 'Active' ? '#166534' : '#991b1b'}; padding: 4px 12px; border-radius: 12px; font-size: ${baseFont * 0.75}px; font-weight: 600;">
                            ${price.status}
                          </span>
                        </td>
                        <td style="padding: 16px; text-align: right;">
                          <button class="edit-price-btn" data-id="${price.id}" style="color: ${config.primary_action_color}; background: none; border: none; cursor: pointer; margin-right: 12px; font-size: ${baseFont * 0.875}px; font-weight: 600;">Edit</button>
                          <button class="delete-price-btn" data-id="${price.id}" style="color: #dc2626; background: none; border: none; cursor: pointer; font-size: ${baseFont * 0.875}px; font-weight: 600;">Delete</button>
                        </td>
                      </tr>
                    `).join('')}
                  </tbody>
                </table>
              </div>
            </div>
          `}
        </div>
      `;
    }

    function renderModal() {
      const baseFont = config.font_size;
      const modalContainer = document.getElementById('modal-container');
      
      let modalContent = '';
      
      if (modalType === 'feature') {
        modalContent = renderFeatureModal(baseFont);
      } else if (modalType === 'package') {
        modalContent = renderPackageModal(baseFont);
      } else if (modalType === 'price') {
        modalContent = renderPriceModal(baseFont);
      } else if (modalType === 'help') {
        modalContent = renderHelpModal(baseFont);
      } else if (modalType === 'copy-error') {
        modalContent = renderCopyErrorModal(baseFont);
      }

      modalContainer.innerHTML = `
        <div class="modal-backdrop" style="position: fixed; inset: 0; background-color: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 50;">
          <div class="slide-in" style="background-color: ${config.surface_color}; border-radius: 16px; max-width: 600px; width: 90%; max-height: 90%; overflow-y: auto; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1);">
            ${modalContent}
          </div>
        </div>
      `;

      setupModalListeners();
      
      // Special handler for copy error modal
      if (modalType === 'copy-error') {
        const selectAllBtn = document.getElementById('select-all-btn');
        if (selectAllBtn) {
          selectAllBtn.addEventListener('click', () => {
            const textarea = document.getElementById('html-code-area');
            if (textarea) {
              textarea.select();
              textarea.setSelectionRange(0, textarea.value.length);
              showToast('Text selected! Now press Ctrl+C (or Cmd+C) to copy', 'success');
            }
          });
        }
      }
    }

    function renderFeatureModal(baseFont) {
      const isEdit = editingItem !== null;
      const feature = isEdit ? editingItem : { name: '', key: '', dataType: 'Number', isMetered: false, description: '', status: 'Active' };

      return `
        <div style="padding: 32px;">
          <h3 style="font-size: ${baseFont * 1.5}px; color: ${config.text_color}; font-weight: 700; margin: 0 0 24px 0;">
            ${isEdit ? 'Edit Feature' : 'Add New Feature'}
          </h3>

          <form id="feature-form">
            <div style="margin-bottom: 20px;">
              <label style="display: block; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; font-weight: 600; margin-bottom: 8px;">
                Feature Name *
              </label>
              <input type="text" id="feature-name" value="${feature.name}" required style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont}px;">
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display: block; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; font-weight: 600; margin-bottom: 8px;">
                Feature Key *
              </label>
              <input type="text" id="feature-key" value="${feature.key}" required style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont}px; font-family: monospace;">
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display: block; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; font-weight: 600; margin-bottom: 8px;">
                Data Type *
              </label>
              <select id="feature-datatype" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont}px;">
                <option value="Number" ${feature.dataType === 'Number' ? 'selected' : ''}>Number</option>
                <option value="Boolean" ${feature.dataType === 'Boolean' ? 'selected' : ''}>Boolean</option>
                <option value="Text" ${feature.dataType === 'Text' ? 'selected' : ''}>Text</option>
              </select>
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-size: ${baseFont}px; color: ${config.text_color};">
                <input type="checkbox" id="feature-metered" ${feature.isMetered ? 'checked' : ''} style="width: 20px; height: 20px; cursor: pointer;">
                <span>Is Metered?</span>
              </label>
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display: block; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; font-weight: 600; margin-bottom: 8px;">
                Description
              </label>
              <textarea id="feature-description" rows="3" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont}px; resize: vertical;">${feature.description}</textarea>
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display: block; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; font-weight: 600; margin-bottom: 8px;">
                Status
              </label>
              <select id="feature-status" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont}px;">
                <option value="Active" ${feature.status === 'Active' ? 'selected' : ''}>Active</option>
                <option value="Inactive" ${feature.status === 'Inactive' ? 'selected' : ''}>Inactive</option>
              </select>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
              <button type="button" id="modal-cancel" style="padding: 12px 24px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont}px; font-weight: 600; background: none; cursor: pointer; color: ${config.secondary_action_color};">
                Cancel
              </button>
              <button type="submit" id="modal-save" style="padding: 12px 24px; background-color: ${config.primary_action_color}; color: white; border-radius: 8px; font-size: ${baseFont}px; font-weight: 600; border: none; cursor: pointer;">
                ${isEdit ? 'Update' : 'Create'} Feature
              </button>
            </div>
          </form>
        </div>
      `;
    }

    function renderPackageModal(baseFont) {
      const isEdit = editingItem !== null;
      const pkg = isEdit ? editingItem : { packageName: '', packageCode: '', description: '', billingType: 'Monthly', trialEnabled: false, trialDays: 0, creditCardRequired: true, status: 'Active', sortOrder: 0 };

      return `
        <div style="padding: 32px;">
          <h3 style="font-size: ${baseFont * 1.5}px; color: ${config.text_color}; font-weight: 700; margin: 0 0 24px 0;">
            ${isEdit ? 'Edit Package' : 'Create New Package'}
          </h3>

          <form id="package-form">
            <div style="margin-bottom: 20px;">
              <label style="display: block; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; font-weight: 600; margin-bottom: 8px;">
                Package Name *
              </label>
              <input type="text" id="package-name" value="${pkg.packageName}" required style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont}px;">
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display: block; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; font-weight: 600; margin-bottom: 8px;">
                Package Code *
              </label>
              <input type="text" id="package-code" value="${pkg.packageCode}" required style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont}px; font-family: monospace;">
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display: block; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; font-weight: 600; margin-bottom: 8px;">
                Description
              </label>
              <textarea id="package-description" rows="3" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont}px; resize: vertical;">${pkg.description}</textarea>
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display: block; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; font-weight: 600; margin-bottom: 12px;">
                Billing Type *
              </label>
              <div style="display: flex; gap: 16px;">
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                  <input type="checkbox" id="billing-monthly" ${pkg.billingType.includes('Monthly') ? 'checked' : ''} style="width: 18px; height: 18px;">
                  <span style="font-size: ${baseFont * 0.875}px;">Monthly</span>
                </label>
                <label style="display: flex; align-items: center; gap: 8px; cursor: pointer;">
                  <input type="checkbox" id="billing-annual" ${pkg.billingType.includes('Annual') ? 'checked' : ''} style="width: 18px; height: 18px;">
                  <span style="font-size: ${baseFont * 0.875}px;">Annual</span>
                </label>
              </div>
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-size: ${baseFont}px; color: ${config.text_color};">
                <input type="checkbox" id="trial-enabled" ${pkg.trialEnabled ? 'checked' : ''} style="width: 20px; height: 20px; cursor: pointer;">
                <span>Enable Trial Period</span>
              </label>
            </div>

            <div id="trial-days-container" style="margin-bottom: 20px; ${pkg.trialEnabled ? '' : 'display: none;'}">
              <label style="display: block; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; font-weight: 600; margin-bottom: 8px;">
                Trial Days
              </label>
              <input type="number" id="trial-days" value="${pkg.trialDays}" min="0" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont}px;">
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display: flex; align-items: center; gap: 12px; cursor: pointer; font-size: ${baseFont}px; color: ${config.text_color};">
                <input type="checkbox" id="credit-card-required" ${pkg.creditCardRequired ? 'checked' : ''} style="width: 20px; height: 20px; cursor: pointer;">
                <span>Credit Card Required</span>
              </label>
              <p style="font-size: ${baseFont * 0.75}px; color: ${config.secondary_action_color}; margin: 8px 0 0 32px; line-height: 1.4;">
                When enabled, customers must provide payment information during signup (even for free trials)
              </p>
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display: block; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; font-weight: 600; margin-bottom: 8px;">
                Status
              </label>
              <select id="package-status" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont}px;">
                <option value="Active" ${pkg.status === 'Active' ? 'selected' : ''}>Active</option>
                <option value="Inactive" ${pkg.status === 'Inactive' ? 'selected' : ''}>Inactive</option>
              </select>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
              <button type="button" id="modal-cancel" style="padding: 12px 24px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont}px; font-weight: 600; background: none; cursor: pointer; color: ${config.secondary_action_color};">
                Cancel
              </button>
              <button type="submit" id="modal-save" style="padding: 12px 24px; background-color: ${config.primary_action_color}; color: white; border-radius: 8px; font-size: ${baseFont}px; font-weight: 600; border: none; cursor: pointer;">
                ${isEdit ? 'Update' : 'Create'} Package
              </button>
            </div>
          </form>
        </div>
      `;
    }

    function renderPriceModal(baseFont) {
      const isEdit = editingItem !== null;
      const price = isEdit ? editingItem : { billingCycle: 'Monthly', price: 0, status: 'Active' };

      const selectedPkg = packages.find(p => p.id === selectedPackage);
      const availableCycles = selectedPkg ? selectedPkg.billingType.split(',').map(t => t.trim()) : [];
      
      const existingPricing = pricing.filter(p => 
        p.packageId === selectedPackage && 
        p.currency === selectedCurrency &&
        (!isEdit || p.id !== editingItem.id)
      );
      const existingCycles = existingPricing.map(p => p.billingCycle);
      
      const selectableCycles = isEdit 
        ? availableCycles.filter(cycle => cycle === price.billingCycle || !existingCycles.includes(cycle))
        : availableCycles.filter(cycle => !existingCycles.includes(cycle));

      return `
        <div style="padding: 32px;">
          <h3 style="font-size: ${baseFont * 1.5}px; color: ${config.text_color}; font-weight: 700; margin: 0 0 24px 0;">
            ${isEdit ? 'Edit Price' : 'Add New Price'}
          </h3>

          <form id="price-form">
            ${selectableCycles.length === 0 ? `
              <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
                <p style="font-size: ${baseFont * 0.875}px; color: #92400e; margin: 0;">
                  ⚠️ All available billing cycles have been configured for this package and currency.
                </p>
              </div>
            ` : ''}

            <div style="margin-bottom: 20px;">
              <label style="display: block; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; font-weight: 600; margin-bottom: 8px;">
                Package
              </label>
              <input type="text" value="${selectedPkg?.packageName || ''}" disabled style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont}px; background-color: #f1f5f9; color: #64748b; cursor: not-allowed;">
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display: block; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; font-weight: 600; margin-bottom: 8px;">
                Currency
              </label>
              <input type="text" value="${selectedCurrency}" disabled style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont}px; background-color: #f1f5f9; color: #64748b; cursor: not-allowed;">
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display: block; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; font-weight: 600; margin-bottom: 8px;">
                Billing Cycle *
              </label>
              <select id="price-billing-cycle" ${selectableCycles.length === 0 ? 'disabled' : ''} style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont}px; ${selectableCycles.length === 0 ? 'background-color: #f1f5f9; color: #64748b; cursor: not-allowed;' : ''}">
                ${selectableCycles.map(cycle => `
                  <option value="${cycle}" ${price.billingCycle === cycle ? 'selected' : ''}>${cycle}</option>
                `).join('')}
              </select>
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display: block; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; font-weight: 600; margin-bottom: 8px;">
                Price (${selectedCurrency}) *
              </label>
              <input type="number" id="price-amount" value="${price.price}" required min="0" step="0.01" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont}px;">
            </div>

            <div style="margin-bottom: 20px;">
              <label style="display: block; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; font-weight: 600; margin-bottom: 8px;">
                Status
              </label>
              <select id="price-status" style="width: 100%; padding: 12px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont}px;">
                <option value="Active" ${price.status === 'Active' ? 'selected' : ''}>Active</option>
                <option value="Inactive" ${price.status === 'Inactive' ? 'selected' : ''}>Inactive</option>
              </select>
            </div>

            <div style="display: flex; gap: 12px; justify-content: flex-end;">
              <button type="button" id="modal-cancel" style="padding: 12px 24px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont}px; font-weight: 600; background: none; cursor: pointer; color: ${config.secondary_action_color};">
                Cancel
              </button>
              <button type="submit" id="modal-save" ${selectableCycles.length === 0 ? 'disabled' : ''} style="padding: 12px 24px; background-color: ${selectableCycles.length > 0 ? config.primary_action_color : '#cbd5e1'}; color: white; border-radius: 8px; font-size: ${baseFont}px; font-weight: 600; border: none; cursor: ${selectableCycles.length > 0 ? 'pointer' : 'not-allowed'};">
                ${isEdit ? 'Update' : 'Add'} Price
              </button>
            </div>
          </form>
        </div>
      `;
    }

    function renderHelpModal(baseFont) {
      return `
        <div style="padding: 32px; max-height: 80vh; overflow-y: auto;">
          <h3 style="font-size: ${baseFont * 1.75}px; color: ${config.text_color}; font-weight: 700; margin: 0 0 8px 0;">
            📖 Subscription Management Guide
          </h3>
          <p style="font-size: ${baseFont * 0.875}px; color: ${config.secondary_action_color}; margin: 0 0 32px 0;">
            Complete guide to managing your subscription system
          </p>

          <!-- Overview -->
          <div style="background-color: #eff6ff; border-left: 4px solid ${config.primary_action_color}; padding: 20px; border-radius: 8px; margin-bottom: 32px;">
            <h4 style="font-size: ${baseFont * 1.125}px; color: ${config.text_color}; font-weight: 700; margin: 0 0 12px 0;">
              🎯 System Overview
            </h4>
            <p style="font-size: ${baseFont * 0.875}px; color: ${config.text_color}; line-height: 1.6; margin: 0;">
              This admin panel allows you to define subscription features, create packages (plans), configure feature limits per package, and set pricing in multiple currencies. Follow the workflow below for best results.
            </p>
          </div>

          <!-- Workflow -->
          <div style="background-color: #f0fdf4; border-left: 4px solid #10b981; padding: 20px; border-radius: 8px; margin-bottom: 32px;">
            <h4 style="font-size: ${baseFont * 1.125}px; color: ${config.text_color}; font-weight: 700; margin: 0 0 16px 0;">
              🔄 Recommended Workflow
            </h4>
            <ol style="margin: 0; padding-left: 24px; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; line-height: 1.8;">
              <li style="margin-bottom: 12px;"><strong>Step 1: Define Features</strong> - Go to Features tab and create all features (projects, storage, API access, etc.)</li>
              <li style="margin-bottom: 12px;"><strong>Step 2: Create Packages</strong> - Go to Packages tab and create subscription tiers (Basic, Pro, Enterprise)</li>
              <li style="margin-bottom: 12px;"><strong>Step 3: Configure Features</strong> - Go to Configuration tab and set feature limits for each package</li>
              <li style="margin-bottom: 0;"><strong>Step 4: Set Pricing</strong> - Go to Pricing tab and add prices for each billing cycle and currency</li>
            </ol>
          </div>

          <!-- Features Section -->
          <div style="margin-bottom: 32px;">
            <h4 style="font-size: ${baseFont * 1.25}px; color: ${config.text_color}; font-weight: 700; margin: 0 0 16px 0; padding-bottom: 12px; border-bottom: 2px solid #e2e8f0;">
              📋 Features Tab
            </h4>
            <div style="background-color: ${config.surface_color}; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0;">
              <p style="font-size: ${baseFont * 0.875}px; color: ${config.text_color}; line-height: 1.6; margin: 0 0 16px 0;">
                Features are the building blocks of your subscription system. They represent what customers can access or use.
              </p>
              <ul style="margin: 0; padding-left: 20px; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; line-height: 1.8;">
                <li style="margin-bottom: 8px;"><strong>Feature Name:</strong> User-friendly name (e.g., "Projects", "Storage")</li>
                <li style="margin-bottom: 8px;"><strong>Feature Key:</strong> System identifier auto-generated from name (e.g., "projects", "storage_gb")</li>
                <li style="margin-bottom: 8px;"><strong>Data Type:</strong>
                  <ul style="margin-top: 4px; padding-left: 20px;">
                    <li><em>Number</em> - For countable limits (5 projects, 100GB storage)</li>
                    <li><em>Boolean</em> - For on/off features (API Access: Yes/No)</li>
                    <li><em>Text</em> - For text-based features (rarely used)</li>
                  </ul>
                </li>
                <li style="margin-bottom: 8px;"><strong>Is Metered:</strong> Check if you track usage for billing (e.g., API calls, storage used)</li>
                <li style="margin-bottom: 0;"><strong>Status:</strong> Active (visible) or Inactive (hidden but preserved)</li>
              </ul>
            </div>
          </div>

          <!-- Packages Section -->
          <div style="margin-bottom: 32px;">
            <h4 style="font-size: ${baseFont * 1.25}px; color: ${config.text_color}; font-weight: 700; margin: 0 0 16px 0; padding-bottom: 12px; border-bottom: 2px solid #e2e8f0;">
              📦 Packages Tab
            </h4>
            <div style="background-color: ${config.surface_color}; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0;">
              <p style="font-size: ${baseFont * 0.875}px; color: ${config.text_color}; line-height: 1.6; margin: 0 0 16px 0;">
                Packages are subscription tiers (plans) that customers can purchase. Common structure: Free → Basic → Pro → Enterprise.
              </p>
              <ul style="margin: 0; padding-left: 20px; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; line-height: 1.8;">
                <li style="margin-bottom: 8px;"><strong>Package Name:</strong> Customer-facing name (e.g., "Professional Plan")</li>
                <li style="margin-bottom: 8px;"><strong>Package Code:</strong> System identifier auto-generated (e.g., "PRO", "ENTERPRISE")</li>
                <li style="margin-bottom: 8px;"><strong>Billing Type:</strong> Select Monthly, Annual, or both (recommended: both for flexibility)</li>
                <li style="margin-bottom: 8px;"><strong>Trial Period:</strong> 
                  <ul style="margin-top: 4px; padding-left: 20px;">
                    <li>Enable to offer free trial (7-30 days typical)</li>
                    <li>Set trial duration in days</li>
                  </ul>
                </li>
                <li style="margin-bottom: 0;"><strong>Credit Card Required:</strong> Check if payment info needed during signup (even for trials)</li>
              </ul>
            </div>
          </div>

          <!-- Configuration Section -->
          <div style="margin-bottom: 32px;">
            <h4 style="font-size: ${baseFont * 1.25}px; color: ${config.text_color}; font-weight: 700; margin: 0 0 16px 0; padding-bottom: 12px; border-bottom: 2px solid #e2e8f0;">
              ⚙️ Configuration Tab
            </h4>
            <div style="background-color: ${config.surface_color}; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0;">
              <p style="font-size: ${baseFont * 0.875}px; color: ${config.text_color}; line-height: 1.6; margin: 0 0 16px 0;">
                Configure what features are available in each package and set their limits.
              </p>
              <ul style="margin: 0; padding-left: 20px; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; line-height: 1.8;">
                <li style="margin-bottom: 8px;"><strong>Limited:</strong> Set specific number (e.g., 5 projects, 10GB storage)
                  <ul style="margin-top: 4px; padding-left: 20px;">
                    <li>Use for Number-type features</li>
                    <li>Enter limit value in the input field</li>
                  </ul>
                </li>
                <li style="margin-bottom: 8px;"><strong>Unlimited:</strong> No restrictions (typically for premium packages)</li>
                <li style="margin-bottom: 8px;"><strong>Disabled:</strong> Feature not available in this package</li>
                <li style="margin-bottom: 0;"><strong>Example:</strong> Basic might have 5 projects (Limited), Pro has 50 (Limited), Enterprise has Unlimited</li>
              </ul>
            </div>
          </div>

          <!-- Pricing Section -->
          <div style="margin-bottom: 32px;">
            <h4 style="font-size: ${baseFont * 1.25}px; color: ${config.text_color}; font-weight: 700; margin: 0 0 16px 0; padding-bottom: 12px; border-bottom: 2px solid #e2e8f0;">
              💰 Pricing Tab
            </h4>
            <div style="background-color: ${config.surface_color}; padding: 20px; border-radius: 8px; border: 1px solid #e2e8f0;">
              <p style="font-size: ${baseFont * 0.875}px; color: ${config.text_color}; line-height: 1.6; margin: 0 0 16px 0;">
                Set prices for your packages in different currencies and billing cycles.
              </p>
              <ul style="margin: 0; padding-left: 20px; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; line-height: 1.8;">
                <li style="margin-bottom: 8px;"><strong>Multi-Currency:</strong> Add prices in USD, EUR, GBP, INR, AUD</li>
                <li style="margin-bottom: 8px;"><strong>Billing Cycles:</strong> Each package can have Monthly and/or Annual pricing</li>
                <li style="margin-bottom: 8px;"><strong>Pricing Strategy:</strong>
                  <ul style="margin-top: 4px; padding-left: 20px;">
                    <li>Offer 15-20% discount on annual plans</li>
                    <li>Use psychological pricing ($29 vs $30, $99 vs $100)</li>
                    <li>Set $0.00 for free tier packages</li>
                  </ul>
                </li>
                <li style="margin-bottom: 0;"><strong>One Price Per Cycle:</strong> Each package can only have one price per billing cycle per currency (no duplicates)</li>
              </ul>
            </div>
          </div>

          <!-- Best Practices -->
          <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 20px; border-radius: 8px; margin-bottom: 24px;">
            <h4 style="font-size: ${baseFont * 1.125}px; color: ${config.text_color}; font-weight: 700; margin: 0 0 12px 0;">
              💡 Best Practices
            </h4>
            <ul style="margin: 0; padding-left: 20px; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; line-height: 1.8;">
              <li style="margin-bottom: 8px;">Create 3-4 packages maximum (avoid overwhelming customers)</li>
              <li style="margin-bottom: 8px;">Use clear, descriptive feature names that customers understand</li>
              <li style="margin-bottom: 8px;">Test your configuration by viewing each package's features</li>
              <li style="margin-bottom: 8px;">Set inactive status to temporarily hide items without deleting</li>
              <li style="margin-bottom: 0;">Add both Monthly and Annual billing for maximum flexibility</li>
            </ul>
          </div>

          <!-- Example Configuration -->
          <div style="background-color: #f1f5f9; padding: 20px; border-radius: 8px; margin-bottom: 24px;">
            <h4 style="font-size: ${baseFont * 1.125}px; color: ${config.text_color}; font-weight: 700; margin: 0 0 12px 0;">
              📊 Example Setup
            </h4>
            <div style="font-size: ${baseFont * 0.875}px; color: ${config.text_color}; line-height: 1.6;">
              <p style="margin: 0 0 12px 0;"><strong>Feature:</strong> Projects (Number type, not metered)</p>
              <ul style="margin: 0 0 16px 0; padding-left: 20px;">
                <li>Basic Plan: Limited to 5</li>
                <li>Pro Plan: Limited to 50</li>
                <li>Enterprise: Unlimited</li>
              </ul>
              <p style="margin: 0 0 12px 0;"><strong>Feature:</strong> API Access (Boolean type)</p>
              <ul style="margin: 0 0 16px 0; padding-left: 20px;">
                <li>Basic Plan: Disabled</li>
                <li>Pro Plan: Unlimited (enabled)</li>
                <li>Enterprise: Unlimited (enabled)</li>
              </ul>
              <p style="margin: 0 0 12px 0;"><strong>Pricing (USD):</strong></p>
              <ul style="margin: 0; padding-left: 20px;">
                <li>Basic: $0/month (free tier)</li>
                <li>Pro: $29/month or $290/year (17% annual discount)</li>
                <li>Enterprise: $99/month or $990/year (17% annual discount)</li>
              </ul>
            </div>
          </div>

          <div style="display: flex; justify-content: flex-end; padding-top: 16px; border-top: 1px solid #e2e8f0;">
            <button id="modal-cancel" style="padding: 12px 32px; background-color: ${config.primary_action_color}; color: white; border-radius: 8px; font-size: ${baseFont}px; font-weight: 600; border: none; cursor: pointer;">
              Got it, thanks!
            </button>
          </div>
        </div>
      `;
    }

    function renderCopyErrorModal(baseFont) {
      const htmlCode = document.documentElement.outerHTML;
      
      return `
        <div style="padding: 32px; max-width: 800px;">
          <h3 style="font-size: ${baseFont * 1.5}px; color: ${config.text_color}; font-weight: 700; margin: 0 0 16px 0;">
            📋 Copy HTML Code
          </h3>
          
          <div style="background-color: #eff6ff; border-left: 4px solid ${config.primary_action_color}; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
            <p style="font-size: ${baseFont * 0.875}px; color: ${config.text_color}; line-height: 1.6; margin: 0;">
              <strong>Easy Copy Method:</strong> Click inside the text area below, then press <strong>Ctrl+A</strong> (or <strong>Cmd+A</strong> on Mac) to select all, then <strong>Ctrl+C</strong> (or <strong>Cmd+C</strong>) to copy!
            </p>
          </div>

          <div style="margin-bottom: 20px;">
            <label style="display: block; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; font-weight: 600; margin-bottom: 8px;">
              HTML Code (Click inside and press Ctrl+A, then Ctrl+C):
            </label>
            <textarea 
              id="html-code-area" 
              readonly 
              style="width: 100%; height: 400px; padding: 16px; border: 2px solid #e2e8f0; border-radius: 8px; font-size: ${baseFont * 0.75}px; font-family: monospace; resize: vertical; background-color: #f8fafc; color: ${config.text_color}; line-height: 1.4;"
            >${htmlCode.replace(/</g, '<').replace(/>/g, '>')}</textarea>
          </div>

          <div style="background-color: #f0fdf4; border-left: 4px solid #10b981; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
            <p style="font-size: ${baseFont * 0.875}px; color: ${config.text_color}; margin: 0 0 8px 0;">
              <strong>✅ Step-by-step:</strong>
            </p>
            <ol style="margin: 0; padding-left: 20px; font-size: ${baseFont * 0.875}px; color: ${config.text_color}; line-height: 1.8;">
              <li>Click inside the text area above</li>
              <li>Press <strong>Ctrl+A</strong> (Windows) or <strong>Cmd+A</strong> (Mac) to select all</li>
              <li>Press <strong>Ctrl+C</strong> (Windows) or <strong>Cmd+C</strong> (Mac) to copy</li>
              <li>Paste it into your text editor or file</li>
            </ol>
          </div>

          <div style="display: flex; gap: 12px; justify-content: flex-end;">
            <button id="select-all-btn" style="padding: 12px 24px; background-color: #10b981; color: white; border-radius: 8px; font-size: ${baseFont}px; font-weight: 600; border: none; cursor: pointer;">
              Select All Text
            </button>
            <button id="modal-cancel" style="padding: 12px 24px; background-color: ${config.secondary_action_color}; color: white; border-radius: 8px; font-size: ${baseFont}px; font-weight: 600; border: none; cursor: pointer;">
              Close
            </button>
          </div>
        </div>
      `;
    }

    function setupFeaturesListeners() {
      const addBtn = document.getElementById('add-feature-btn');
      if (addBtn) {
        addBtn.addEventListener('click', () => {
          showModal = true;
          modalType = 'feature';
          editingItem = null;
          renderApp();
        });
      }

      document.querySelectorAll('.edit-feature-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const id = e.target.dataset.id;
          editingItem = features.find(f => f.id === id);
          showModal = true;
          modalType = 'feature';
          renderApp();
        });
      });

      document.querySelectorAll('.delete-feature-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const id = e.target.dataset.id;
          
          const confirmBtn = e.target;
          const originalText = confirmBtn.textContent;
          confirmBtn.textContent = 'Confirm?';
          confirmBtn.style.fontWeight = '700';
          
          const timeoutId = setTimeout(() => {
            confirmBtn.textContent = originalText;
            confirmBtn.style.fontWeight = '600';
            confirmBtn.onclick = null;
          }, 3000);
          
          confirmBtn.onclick = () => {
            clearTimeout(timeoutId);
            features = features.filter(f => f.id !== id);
            featureConfigs = featureConfigs.filter(fc => fc.featureId !== id);
            saveToLocalStorage();
            renderApp();
          };
        });
      });
    }

    function setupPackagesListeners() {
      const addBtn = document.getElementById('add-package-btn');
      if (addBtn) {
        addBtn.addEventListener('click', () => {
          showModal = true;
          modalType = 'package';
          editingItem = null;
          renderApp();
        });
      }

      document.querySelectorAll('.edit-package-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const id = e.target.dataset.id;
          editingItem = packages.find(p => p.id === id);
          showModal = true;
          modalType = 'package';
          renderApp();
        });
      });

      document.querySelectorAll('.delete-package-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const id = e.target.dataset.id;
          
          const confirmBtn = e.target;
          const originalText = confirmBtn.textContent;
          confirmBtn.textContent = 'Confirm?';
          confirmBtn.style.fontWeight = '700';
          
          const timeoutId = setTimeout(() => {
            confirmBtn.textContent = originalText;
            confirmBtn.style.fontWeight = '600';
            confirmBtn.onclick = null;
          }, 3000);
          
          confirmBtn.onclick = () => {
            clearTimeout(timeoutId);
            packages = packages.filter(p => p.id !== id);
            featureConfigs = featureConfigs.filter(fc => fc.packageId !== id);
            pricing = pricing.filter(pr => pr.packageId !== id);
            if (selectedPackage === id) {
              selectedPackage = null;
            }
            saveToLocalStorage();
            renderApp();
          };
        });
      });
    }

    function setupConfigurationListeners() {
      const packageSelector = document.getElementById('package-selector');
      if (packageSelector) {
        packageSelector.addEventListener('change', (e) => {
          selectedPackage = e.target.value;
          renderContent();
        });
      }

      document.querySelectorAll('.feature-limit-type').forEach(radio => {
        radio.addEventListener('change', (e) => {
          const featureId = e.target.dataset.feature;
          const valueInput = document.querySelector(`.feature-limit-value[data-feature="${featureId}"]`);
          if (valueInput) {
            valueInput.disabled = e.target.value !== 'Limited';
            valueInput.style.opacity = e.target.value !== 'Limited' ? '0.5' : '1';
            valueInput.style.cursor = e.target.value !== 'Limited' ? 'not-allowed' : 'default';
          }
        });
      });

      const saveBtn = document.getElementById('save-config-btn');
      if (saveBtn) {
        saveBtn.addEventListener('click', () => {
          features.forEach(feature => {
            const limitType = document.querySelector(`input[name="limit-${feature.id}"]:checked`)?.value || 'Limited';
            const limitValue = parseInt(document.querySelector(`.feature-limit-value[data-feature="${feature.id}"]`)?.value || '0');
            
            const existingConfigIndex = featureConfigs.findIndex(fc => 
              fc.packageId === selectedPackage && fc.featureId === feature.id
            );

            if (existingConfigIndex >= 0) {
              featureConfigs[existingConfigIndex] = {
                ...featureConfigs[existingConfigIndex],
                limitType,
                limitValue
              };
            } else {
              featureConfigs.push({
                id: generateId(),
                packageId: selectedPackage,
                featureId: feature.id,
                limitType,
                limitValue,
                createdAt: new Date().toISOString()
              });
            }
          });

          saveToLocalStorage();
          showToast('Configuration saved successfully', 'success');
        });
      }
    }

    function setupPricingListeners() {
      const packageSelector = document.getElementById('pricing-package-selector');
      if (packageSelector) {
        packageSelector.addEventListener('change', (e) => {
          selectedPackage = e.target.value;
          renderContent();
        });
      }

      const currencySelector = document.getElementById('currency-selector');
      if (currencySelector) {
        currencySelector.addEventListener('change', (e) => {
          selectedCurrency = e.target.value;
          renderContent();
        });
      }

      const addBtn = document.getElementById('add-price-btn');
      if (addBtn && !addBtn.disabled) {
        addBtn.addEventListener('click', () => {
          showModal = true;
          modalType = 'price';
          editingItem = null;
          renderApp();
        });
      }

      document.querySelectorAll('.edit-price-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const priceId = e.target.dataset.id;
          editingItem = pricing.find(p => p.id === priceId);
          showModal = true;
          modalType = 'price';
          renderApp();
        });
      });

      document.querySelectorAll('.delete-price-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const priceId = e.target.dataset.id;
          
          const confirmBtn = e.target;
          const originalText = confirmBtn.textContent;
          confirmBtn.textContent = 'Confirm?';
          confirmBtn.style.fontWeight = '700';
          
          const timeoutId = setTimeout(() => {
            confirmBtn.textContent = originalText;
            confirmBtn.style.fontWeight = '600';
            confirmBtn.onclick = null;
          }, 3000);
          
          confirmBtn.onclick = () => {
            clearTimeout(timeoutId);
            pricing = pricing.filter(p => p.id !== priceId);
            saveToLocalStorage();
            renderApp();
          };
        });
      });
    }

    function setupModalListeners() {
      const cancelBtn = document.getElementById('modal-cancel');
      if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
          showModal = false;
          editingItem = null;
          renderApp();
        });
      }

      if (modalType === 'feature') {
        const form = document.getElementById('feature-form');
        const nameInput = document.getElementById('feature-name');
        const keyInput = document.getElementById('feature-key');

        nameInput.addEventListener('input', (e) => {
          if (!editingItem) {
            keyInput.value = e.target.value
              .toLowerCase()
              .replace(/[^a-z0-9]+/g, '_')
              .replace(/^_+|_+$/g, '');
          }
        });

        form.addEventListener('submit', (e) => {
          e.preventDefault();

          const featureData = {
            id: editingItem?.id || generateId(),
            name: document.getElementById('feature-name').value,
            key: document.getElementById('feature-key').value,
            dataType: document.getElementById('feature-datatype').value,
            isMetered: document.getElementById('feature-metered').checked,
            description: document.getElementById('feature-description').value,
            status: document.getElementById('feature-status').value,
            createdAt: editingItem?.createdAt || new Date().toISOString()
          };

          if (editingItem) {
            const index = features.findIndex(f => f.id === editingItem.id);
            features[index] = featureData;
          } else {
            features.push(featureData);
          }

          saveToLocalStorage();
          showModal = false;
          editingItem = null;
          renderApp();
        });
      } else if (modalType === 'package') {
        const form = document.getElementById('package-form');
        const nameInput = document.getElementById('package-name');
        const codeInput = document.getElementById('package-code');
        const trialCheckbox = document.getElementById('trial-enabled');
        const trialContainer = document.getElementById('trial-days-container');

        nameInput.addEventListener('input', (e) => {
          if (!editingItem) {
            codeInput.value = e.target.value
              .toUpperCase()
              .replace(/[^A-Z0-9]+/g, '_')
              .replace(/^_+|_+$/g, '');
          }
        });

        trialCheckbox.addEventListener('change', (e) => {
          trialContainer.style.display = e.target.checked ? 'block' : 'none';
        });

        form.addEventListener('submit', (e) => {
          e.preventDefault();

          const billingTypes = [];
          if (document.getElementById('billing-monthly').checked) billingTypes.push('Monthly');
          if (document.getElementById('billing-annual').checked) billingTypes.push('Annual');

          const packageData = {
            id: editingItem?.id || generateId(),
            packageName: document.getElementById('package-name').value,
            packageCode: document.getElementById('package-code').value,
            description: document.getElementById('package-description').value,
            billingType: billingTypes.join(', '),
            trialEnabled: document.getElementById('trial-enabled').checked,
            trialDays: parseInt(document.getElementById('trial-days').value) || 0,
            creditCardRequired: document.getElementById('credit-card-required').checked,
            status: document.getElementById('package-status').value,
            sortOrder: editingItem?.sortOrder || packages.length,
            createdAt: editingItem?.createdAt || new Date().toISOString()
          };

          if (editingItem) {
            const index = packages.findIndex(p => p.id === editingItem.id);
            packages[index] = packageData;
          } else {
            packages.push(packageData);
          }

          saveToLocalStorage();
          showModal = false;
          editingItem = null;
          renderApp();
        });
      } else if (modalType === 'price') {
        const form = document.getElementById('price-form');

        form.addEventListener('submit', (e) => {
          e.preventDefault();

          const priceData = {
            id: editingItem?.id || generateId(),
            packageId: selectedPackage,
            currency: selectedCurrency,
            billingCycle: document.getElementById('price-billing-cycle').value,
            price: parseFloat(document.getElementById('price-amount').value),
            status: document.getElementById('price-status').value,
            createdAt: editingItem?.createdAt || new Date().toISOString()
          };

          if (editingItem) {
            const index = pricing.findIndex(p => p.id === editingItem.id);
            pricing[index] = priceData;
          } else {
            pricing.push(priceData);
          }

          saveToLocalStorage();
          showModal = false;
          editingItem = null;
          renderApp();
        });
      }
    }

    function showToast(message, type) {
      const baseFont = config.font_size;
      
      const toast = document.createElement('div');
      toast.style.cssText = `
        position: fixed;
        bottom: 24px;
        right: 24px;
        background-color: ${type === 'success' ? '#10b981' : '#ef4444'};
        color: white;
        padding: 16px 24px;
        border-radius: 8px;
        font-size: ${baseFont * 0.875}px;
        font-weight: 600;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        z-index: 100;
        animation: slideIn 0.3s ease-out;
      `;
      toast.textContent = message;
      
      document.body.appendChild(toast);
      
      setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        toast.style.transition = 'all 0.3s ease-out';
        setTimeout(() => toast.remove(), 300);
      }, 3000);
    }

    // Initialize
    seedInitialData();
    renderApp();
  </script>
 <script>(function(){function c(){var b=a.contentDocument||a.contentWindow.document;if(b){var d=b.createElement('script');d.innerHTML="window.__CF$cv$params={r:'9b1f37edc635a716',t:'MTc2NjQwMjMwNy4wMDAwMDA='};var a=document.createElement('script');a.nonce='';a.src='/cdn-cgi/challenge-platform/scripts/jsd/main.js';document.getElementsByTagName('head')[0].appendChild(a);";b.getElementsByTagName('head')[0].appendChild(d)}}if(document.body){var a=document.createElement('iframe');a.height=1;a.width=1;a.style.position='absolute';a.style.top=0;a.style.left=0;a.style.border='none';a.style.visibility='hidden';document.body.appendChild(a);if('loading'!==document.readyState)c();else if(window.addEventListener)document.addEventListener('DOMContentLoaded',c);else{var e=document.onreadystatechange||function(){};document.onreadystatechange=function(b){e(b);'loading'!==document.readyState&&(document.onreadystatechange=e,c())}}}})();</script><iframe height="1" width="1" style="position: absolute; top: 0px; left: 0px; border: none; visibility: hidden;"></iframe>
</body></html>
