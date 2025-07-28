# Voice Announcements Feature

## Overview
The QueueingPro system now includes voice announcements that automatically announce new queue numbers when they are displayed on the counter screens.

## Features

### Automatic Announcements
- When a counter receives a new number, the system automatically announces it
- Announcement format: "Number [Number]" (simplified and clear)
- Only announces when counters are active and have valid numbers
- Handles multiple simultaneous controller clicks with a queue system

### Visual Indicators
- Voice indicator shows on the display when announcements are enabled
- Counter cards briefly highlight when new numbers are announced
- Visual feedback in the admin panel shows current voice status

### Admin Controls
- Toggle voice announcements on/off from the admin dashboard
- Test button to verify voice system is working
- Settings are stored in the database and persist across sessions

## Technical Implementation

### Browser Support
- Uses Web Speech API (SpeechSynthesis)
- Works in modern browsers (Chrome, Firefox, Safari, Edge)
- Gracefully degrades if speech synthesis is not supported

### Voice Settings
- Automatically selects the best available voice
- Prefers Google, Microsoft, or Samantha voices for clarity
- Configurable speech rate (0.6x for better clarity and slower pace)
- Volume and pitch optimized for announcements
- Queue system prevents overlapping announcements

### Database Integration
- Voice announcements setting stored in `settings` table
- Setting key: `voice_announcements_enabled`
- Default value: `1` (enabled)

## Usage

### For Administrators
1. **Enable/Disable**: Use the "Voice: ON/OFF" button in the admin dashboard
2. **Test**: Click "Test Voice" button to verify the system is working
3. **Monitor**: Voice indicator shows on the display when enabled

### For End Users
- Voice announcements happen automatically when new numbers appear
- No user interaction required
- Announcements are clear and professional

## Configuration

### Voice Announcement Text
The announcement format can be modified in `js/display.js` in the `announceNewNumber` method:

```javascript
const announcement = `Number ${number}`;
```

### Speech Settings
Voice parameters can be adjusted in the same method:

```javascript
utterance.rate = 0.6;    // Speech rate (0.1 to 10) - slower for clarity
utterance.pitch = 1.0;   // Pitch (0 to 2)
utterance.volume = 1.0;  // Volume (0 to 1)
```

## Troubleshooting

### No Voice Heard
1. Check if voice announcements are enabled in admin panel
2. Verify browser supports speech synthesis
3. Check system volume and browser audio settings
4. Use the "Test Voice" button to diagnose issues

### Poor Voice Quality
1. Browser may be using a low-quality voice
2. Try refreshing the page to reload voice options
3. Check browser console for available voices

### Multiple Announcements
- Queue system handles multiple simultaneous controller clicks
- Announcements play in sequence without interruption
- Each announcement waits for the previous one to complete
- No announcements are lost or interrupted

## Security Considerations
- Voice announcements only work on the display page
- Admin controls require authentication
- No sensitive data is announced

## Browser Compatibility
- ✅ Chrome/Chromium (best support)
- ✅ Firefox
- ✅ Safari
- ✅ Edge
- ❌ Internet Explorer (not supported)

## Future Enhancements
- Custom announcement templates
- Multiple language support
- Voice selection options
- Announcement scheduling
- Audio file fallbacks 