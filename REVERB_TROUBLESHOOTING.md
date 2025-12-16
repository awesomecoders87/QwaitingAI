# Reverb Real-time Queue Updates - Troubleshooting Guide

## Issue: Queues from `/queue` page not appearing immediately on `/calls` page

### Prerequisites

1. **Start Reverb Server** (REQUIRED):
   ```bash
   php artisan reverb:start
   ```
   Keep this running in a separate terminal window.

2. **Verify Configuration**:
   ```bash
   php check-reverb.php
   ```

### How It Works

1. **Queue Creation** (`/queue` page):
   - When a queue is created, `QueueCreated::dispatch($queueStorage)` is called
   - Event broadcasts to channel: `queue-call.{team_id}`
   - Event name: `queue-call`

2. **Queue Reception** (`/calls` page):
   - Frontend subscribes to `queue-call.{team_id}` channel
   - When event received, dispatches Livewire event `create-queue`
   - Handler `pushLiveQueue()` refreshes the queue list immediately

### Testing Steps

1. **Start Reverb Server**:
   ```bash
   php artisan reverb:start
   ```

2. **Open Browser Console** on `/calls` page:
   - Should see: `✅ Reverb connected successfully`
   - Should see: `✅ Successfully subscribed to queue-call channel`

3. **Create Queue** on `/queue` page:
   - Fill form and submit

4. **Check Console** on `/calls` page:
   - Should see: `📢 Queue-call event received:`
   - Should see: `📢 Queue data:`

5. **Verify Queue Appears**:
   - Queue should appear in "Waiting Visitors" list immediately
   - No page refresh needed

### Common Issues

#### Issue 1: Reverb Server Not Running
**Symptom**: No events received, queues only appear after page refresh

**Solution**: 
```bash
php artisan reverb:start
```

#### Issue 2: Connection Failed
**Symptom**: Console shows `❌ Reverb connection error`

**Check**:
- Reverb server is running
- Port 8080 is not blocked by firewall
- `REVERB_HOST` and `REVERB_PORT` in `.env` match server config

#### Issue 3: Subscription Failed
**Symptom**: Console shows `❌ Failed to subscribe to queue-call channel`

**Check**:
- Channel authorization in `routes/channels.php`
- User has access to the team
- `BROADCAST_DRIVER=reverb` in `.env`

#### Issue 4: Events Received But Queue Not Appearing
**Symptom**: Console shows event received but queue doesn't appear

**Check**:
- Queue location matches current location
- Queue hasn't been called yet (`called_datetime` is null)
- Browser console for JavaScript errors

### Debug Commands

```bash
# Check Reverb status
php check-reverb.php

# Check if port is in use
netstat -ano | findstr :8080

# View Laravel logs
tail -f storage/logs/laravel.log

# Clear cache
php artisan cache:clear
php artisan config:clear
```

### Environment Variables Required

```env
BROADCAST_DRIVER=reverb
REVERB_APP_KEY=your-key
REVERB_APP_SECRET=your-secret
REVERB_APP_ID=your-app-id
REVERB_HOST=127.0.0.1
REVERB_PORT=8080
REVERB_SCHEME=http
```

### Files Modified

- `app/Livewire/QueueCalls.php` - Enhanced `pushLiveQueue()` handler
- `resources/views/livewire/queue-calls.blade.php` - Removed delay, added logging
- `routes/channels.php` - Fixed channel authorization patterns

