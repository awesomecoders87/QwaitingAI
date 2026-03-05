# Singpass Login — User Journey & Integration Guide
> **Template Version:** 1.0 | Updated March 2026  
> **Use Case:** Login API — Singpass as a secure alternative login method  
> **Submission Format:** PDF only (via Singpass Developer Portal)

---

## Table of Contents

1. [Overview & Instructions](#1-overview--instructions)
2. [The 6-Step User Journey](#2-the-6-step-user-journey)
3. [Design Guidelines](#3-design-guidelines)
4. [Media & Branding Assets](#4-media--branding-assets)
5. [Scope Justification Table (Action Required)](#5-scope-justification-table-action-required-)
6. [Reference Journey — Example](#6-reference-journey--example)
7. [Reference Journey — Your Template to Fill](#7-reference-journey--your-template-to-fill)
8. [Laravel Implementation Checklist](#8-laravel-implementation-checklist)

---

## 1. Overview & Instructions

This template guides you in documenting your user journey when integrating **Singpass Login** into your digital service.

> ⚠️ **Submit in PDF format only.** Submissions in other formats will not be processed.  
> Submit via the [Singpass Developer Portal](https://developer.singpass.gov.sg).

---

## 2. The 6-Step User Journey

Every Singpass Login integration must document all 6 of the following steps:

| Step | Name | Description |
|------|------|-------------|
| 1 | **Display Singpass Button** | Provide Singpass as a login option alongside a non-Singpass method |
| 2 | **Singpass Login Page** | Users are redirected to the official Singpass Login page |
| 3 | **Login Approval** | Users approve the login on their Singpass mobile app |
| 4 | **Login Consent** *(if applicable)* | User agrees to share requested data fields |
| 5 | **Post-Login Redirection** | Users are redirected to your specified page upon successful login |
| 6 | **Error Handling** | Display an appropriate error message or prompt if login fails |

---

## 3. Design Guidelines

### Guideline 1 — Provide a Non-Singpass Login Method

> Clearly display and provide a **non-Singpass login or verification method** in your user journey.

Your login page **must always** offer an alternative to Singpass (e.g. email/password login).

**Laravel Implementation:**
```html
<!-- Always show both options -->
<form method="POST" action="/login">
    @csrf
    <input type="email" name="email" placeholder="Email" />
    <input type="password" name="password" placeholder="Password" />
    <button type="submit">Login with Email</button>
</form>

<hr />
<p>Or login with:</p>
<a href="{{ route('singpass.login') }}">
    <img src="{{ asset('images/singpass-btn-horizontal.png') }}" alt="Login with Singpass" height="44" />
</a>
```

---

### Guideline 2 — Adhere to Singpass Login Button Guidelines

> Follow the official [Singpass Login Button Guidelines](https://developer.singpass.gov.sg).

- Use only the **official Singpass button assets** (see [Section 4](#4-media--branding-assets))
- Do **not** alter the button's colour, text, or proportions
- Available in two variants: **stacked** and **horizontal**

---

### Guideline 3 — Include the Post-Login Landing Page

> Include the page that users are brought to **after logging in with Singpass**.

This ensures both Singpass and your company understands the purpose of using Singpass Login.

**Laravel Implementation:**
```php
// After successful token exchange, redirect to a meaningful page
Auth::login($user, true);
return redirect()->intended('/dashboard');
```

---

### Guideline 4 — Guide Unregistered Users to Create an Account

> Guide users who don't have an account to **create one** — do NOT tell them "Singpass login failed".

| ✅ Correct | ❌ Wrong |
|-----------|---------|
| Guide users to create an account with your product | Inform users that they need to create an account by saying the Singpass login has failed — this misleads users |

**Laravel Implementation:**
```php
// In your callback controller
$user = User::where('singpass_sub', $sub)->first();

if (!$user) {
    // ✅ Correct: redirect to registration, not an error
    session(['pending_singpass_sub' => $sub]);
    return redirect()->route('register.singpass')
        ->with('info', 'Please complete your registration to continue.');
}

// ❌ Wrong — never do this:
// abort(401, 'Singpass login failed. No account found.');
```

---

## 4. Media & Branding Assets

Use the official Singpass button images in your UI. Two variants are available:

| Variant | Usage |
|---------|-------|
| **Login button (stacked)** | Use when vertical layout is preferred |
| **Login/Authenticate button (horizontal)** | Use when horizontal layout is preferred |

Download official assets from the [Singpass Developer Portal](https://developer.singpass.gov.sg) or the branding guidelines page.

> For full branding guidelines, refer to the [Singpass Button Guidelines](https://developer.singpass.gov.sg).

---

## 5. Scope Justification Table (Action Required 🚨)

### What is this?

You must justify **every data scope** you intend to retrieve via Singpass Login. This is **mandatory for approval**.

For each scope, explain:
- Why it is necessary
- How it will be used in your application or business process
- Any regulatory requirements (e.g. MAS/IMDA)

---

### Example (Pre-filled Reference)

| S/N | Data Scope | Purpose / Justification |
|-----|------------|--------------------------|
| 1 | `user.identity` | Identity verification & record matching. Use NRIC to uniquely identify the applicant, match against internal customer/application records. |
| 2 | `name` | Form autofill & document consistency. Used to pre-fill the applicant's full name to reduce input errors. |
| 3 | `mobile no.` | Used to contact the applicant on application status, request missing information, and (if applicable) send OTP/verification messages for account or submission confirmation. |
| 4 | `email` | Used to send written communications such as application confirmation, approval/rejection notices. |

---

### Your Table to Complete ✏️

🚨 Fill in the **Purpose/Justification** column for every scope your app will use. Delete rows for scopes you do not need.

| S/N | Data Scope | Purpose / Justification |
|-----|------------|--------------------------|
| 1 | `user.identity` | *(Fill in your justification)* |
| 2 | `name` | *(Fill in your justification or remove if not needed)* |
| 3 | `mobile no.` | *(Fill in your justification or remove if not needed)* |
| 4 | `email` | *(Fill in your justification or remove if not needed)* |

> ✅ **This is mandatory for approval.** Do not leave fields blank.

---

## 6. Reference Journey — Example

This section shows what a completed journey looks like. Use it as reference when building your own.

---

### Step 1 — Display Singpass Button

**Screenshot/Mockup:** Your login page showing the Singpass button

**Requirements ✅**
- Webpage/app shows a "Log in with Singpass" button
- A non-Singpass login/verification method is also displayed

---

### Step 2 — Singpass Login Page

> ⛔ **Do not remove or change this page.**

This is the standard Singpass Login page that users are redirected to. It is fully managed by Singpass — no customisation allowed.

---

### Step 3 — Login Approval Page

> ⛔ **Do not remove or change this page.**

Users approve the login on their Singpass mobile app.

**⭐ Tip:** The **authentication context message** is displayed to users on this page.
- [Learn more about the message](https://developer.singpass.gov.sg)
- [Guidance on writing authentication messages](https://developer.singpass.gov.sg)

> ⚠️ **Note:** Authentication context messages can now be configured, but they will not be displayed to users until the rollout is complete in the coming weeks.

---

### Step 4 — Login Consent Screen

> ⛔ **Do not remove or change this page.**

> ⚠️ **Note:** This step is **only applicable** if you are requesting `name`, `mobile number`, or `email address`.

---

### Step 5 — Post-Login Landing Page (Registered Users)

**Screenshot/Mockup:** The page existing/registered users see after successfully logging in.

**Requirements ✅**
- Display the webpage or app screen users will see after logging in with Singpass

---

### Step 6 — Error / Unregistered User Screen

**Screenshot/Mockup:** The landing page or error prompt shown to unregistered users.

**Requirements ✅**
- Error messages must clearly inform users that **all account-related inquiries should be directed to your system's helpdesk**, not Singpass

---

## 7. Reference Journey — Your Template to Fill

Use this section as your submission template. Replace each placeholder with your own screenshots or mockups.

---

### Step 1 — Display Singpass Button *(Insert your screenshot)*

> Provide a screenshot or mockup of your web page or app showing the option to log in or authenticate using Singpass.

**Requirements ✅**
- Webpage/app showing "Log in with Singpass" button
- A non-Singpass log in/verification method is also displayed

**[ INSERT SCREENSHOT HERE ]**

---

### Step 2 — Singpass Login Page

> ⛔ **Do not remove or change this page.**

**[ USE STANDARD SINGPASS LOGIN PAGE SCREENSHOT ]**

---

### Step 3 — Login Approval Page

> ⛔ **Do not remove or change this page.**

**[ USE STANDARD SINGPASS APPROVAL PAGE SCREENSHOT ]**

---

### Step 4 — Login Consent Screen

> ⛔ **Do not remove or change this page.**

**[ USE STANDARD SINGPASS CONSENT SCREEN SCREENSHOT ]**

---

### Step 5 — Post-Login Landing Page *(Insert your screenshot)*

> Insert an image of the landing page your existing user will be redirected to upon successful login.

**Requirements ✅**
- Display the webpage or app screen users will see after logging in with Singpass

**[ INSERT YOUR POST-LOGIN LANDING PAGE SCREENSHOT HERE ]**

---

### Step 6 — Error / Unregistered User Screen *(Insert your screenshot)*

> Insert an image of the landing page or prompt your non-registered user will be redirected to or receive upon login via Singpass.

**Requirements ✅**
- Error messages clearly inform users that all account-related inquiries should be directed to **your system's helpdesk**, not Singpass

**[ INSERT YOUR ERROR/REGISTRATION REDIRECT SCREEN SCREENSHOT HERE ]**

---

## 8. Laravel Implementation Checklist

Use this checklist before submitting your user journey and going live.

### Pre-Development
- [ ] Register on [Singpass Developer Portal](https://developer.singpass.gov.sg) with Corppass
- [ ] Create a **Login** app and obtain your `client_id`
- [ ] Configure `redirect_uri` (e.g. `https://yourapp.com/singpass/callback`)
- [ ] Register your JWKS URI (e.g. `https://yourapp.com/singpass/jwks`)

### User Journey Submission
- [ ] Fill in **Steps 1–6** in the template (Section 7) with your app screenshots
- [ ] Complete the **Scope Justification Table** (Section 5, Slide 10)
- [ ] Export as **PDF** and submit via the Developer Portal
- [ ] Await approval before going live

### Technical Implementation
- [ ] Generate EC key pairs (P-256) for signing and encryption
- [ ] Store private keys securely (outside `public/`, in `.gitignore`)
- [ ] Implement `/singpass/jwks` endpoint (publicly accessible)
- [ ] Implement PAR (Pushed Authorization Request) flow
- [ ] Implement PKCE (`S256` code challenge)
- [ ] Implement `private_key_jwt` client assertion signing
- [ ] Implement token exchange with `code_verifier`
- [ ] Verify ID token (issuer, audience, expiry, nonce)
- [ ] Extract `sub` claim as the user's unique identifier
- [ ] Handle unregistered users gracefully (redirect to registration, not an error)
- [ ] Ensure non-Singpass login method is always shown alongside the button
- [ ] Use official Singpass button assets only

### Go-Live
- [ ] Test end-to-end on **staging** (`stg-id.singpass.gov.sg`)
- [ ] Switch `SINGPASS_DISCOVERY_URL` to production (`id.singpass.gov.sg`)
- [ ] Confirm JWKS endpoint is publicly reachable in production

---

## Key Environment Variables

```env
SINGPASS_CLIENT_ID=your_client_id
SINGPASS_REDIRECT_URI=https://yourapp.com/singpass/callback
SINGPASS_SCOPES="openid"
SINGPASS_JWKS_URI=https://yourapp.com/singpass/jwks

# Staging
SINGPASS_DISCOVERY_URL=https://stg-id.singpass.gov.sg/.well-known/openid-configuration

# Production (switch when ready)
# SINGPASS_DISCOVERY_URL=https://id.singpass.gov.sg/.well-known/openid-configuration
```

---

## Key Routes (Laravel)

```php
Route::get('/singpass/login',    [SingpassController::class, 'redirect'])->name('singpass.login');
Route::get('/singpass/callback', [SingpassController::class, 'callback'])->name('singpass.callback');
Route::get('/singpass/jwks',     [SingpassController::class, 'jwks'])->name('singpass.jwks');
```

---

## Resources

| Resource | Link |
|----------|------|
| Singpass Developer Portal | https://developer.singpass.gov.sg |
| Singpass Developer Docs | https://docs.developer.singpass.gov.sg |
| Partner Helpdesk | https://partnersupport.singpass.gov.sg |
| User Journey Page | https://docs.developer.singpass.gov.sg/docs/getting-started/user-journey |
| OIDC Integration Guide | https://docs.developer.singpass.gov.sg/docs/technical-specifications/integration-guide |
| Myinfo Data Catalog | https://docs.developer.singpass.gov.sg/docs/data-catalog-myinfo/catalog |

---

*Last updated: March 2026 | FAPI 2.0 migration required by 31 Dec 2026*
