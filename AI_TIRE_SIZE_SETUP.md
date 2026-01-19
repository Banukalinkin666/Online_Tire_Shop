# AI Tire Size Detection Setup Guide (FREE VERSION)

This guide explains how to set up **FREE** AI-powered tire size detection from VIN numbers.

---

## How It Works

### Current Flow (Without AI)
1. User enters VIN
2. System decodes VIN → Gets Year/Make/Model
3. System searches database for vehicle → Gets tire sizes
4. If vehicle not in database → User must manually enter tire sizes

### New Flow (With AI - FREE)
1. User enters VIN
2. System decodes VIN → Gets Year/Make/Model
3. **AI determines tire sizes (FREE)** → Shows tire sizes immediately
4. System searches database for matching tires
5. If vehicle not in database → Pre-fills form with AI tire sizes

---

## Supported AI Provider (FREE)

### Google Gemini (FREE Tier)
- **API:** Google Gemini API
- **Model:** gemini-pro (FREE)
- **Cost:** **$0.00** - Completely FREE!
- **Free Tier:** 15 requests per minute, 1500 requests per day
- **Speed:** Fast (~1-2 seconds)
- **No Credit Card Required:** For free tier

---

## Setup Instructions (FREE)

### Google Gemini (FREE - No Credit Card Required)

1. **Get FREE API Key**
   - Go to https://makersuite.google.com/app/apikey
   - Sign in with Google account (free)
   - Click "Create API Key"
   - Copy the key (starts with `AIza...`)

2. **Add to Render Environment Variables**
   - Go to Render → Your Web Service → Environment
   - Add: `GEMINI_API_KEY` = `AIza...` (your free API key)
   - Save changes

3. **Test**
   - Enter a VIN
   - System will automatically use FREE AI to detect tire sizes
   - Tire sizes will be pre-filled in the form
   - **No charges!** Completely free!

### Free Tier Limits
- **15 requests per minute**
- **1,500 requests per day**
- Perfect for most tire shops!
- If you need more, upgrade to paid tier (still very cheap)

---

## How It Works in Code

### 1. VIN Decode (`api/vin.php`)
```php
// After VIN decode, try AI tire size detection
$aiTireService = new AITireSizeService();
$aiTireSizes = $aiTireService->getTireSizesFromAI(
    $vehicleInfo['year'],
    $vehicleInfo['make'],
    $vehicleInfo['model'],
    $vehicleInfo['trim'] ?? null,
    $vehicleInfo['body_class'] ?? null,
    $vehicleInfo['drive_type'] ?? null
);
```

### 2. AI Service (`app/Services/AITireSizeService.php`)
- Tries OpenAI first
- Falls back to Anthropic if OpenAI fails
- Returns tire sizes in format: `{"front_tire": "225/65R17", "rear_tire": null}`

### 3. Frontend (`public/assets/js/app.js`)
- If AI tire sizes available → Shows them immediately
- Pre-fills add vehicle form with AI tire sizes
- User can verify and confirm

---

## User Experience

### With AI Enabled:
1. User enters VIN: `19XFC2F59GE123456`
2. System decodes: `2015 Toyota RAV4`
3. **AI detects tire sizes:** `225/65R17` (front), `225/65R17` (rear)
4. **Shows immediately:**
   ```
   Vehicle Information (from VIN):
   - Year: 2015
   - Make: Toyota
   - Model: RAV4
   
   Recommended Tire Sizes (AI Detected):
   - Front: 225/65R17
   - Rear: 225/65R17
   ```
5. Form pre-filled with AI tire sizes
6. User verifies and clicks "Add Vehicle & Continue"

### Without AI (Fallback):
- Works as before
- User must manually enter tire sizes

---

## Cost Estimation

### Google Gemini (FREE Tier)
- **Per request:** **$0.00** (FREE!)
- **1000 requests:** **$0.00** (FREE!)
- **10,000 requests:** **$0.00** (FREE!)
- **Daily limit:** 1,500 requests (FREE)
- **Rate limit:** 15 requests/minute (FREE)

**Cost:** **$0.00** - Completely FREE! No credit card needed for free tier!

**If you exceed free tier:** Paid tier is ~$0.00025 per request (very cheap)

---

## Security Notes

1. **API Keys:** Never commit API keys to Git
2. **Environment Variables:** Store keys in Render environment variables only
3. **Rate Limiting:** Consider adding rate limiting for production
4. **Error Handling:** System gracefully falls back if AI fails

---

## Testing

### Test AI is Working:
1. Set `OPENAI_API_KEY` in environment variables
2. Enter a VIN (any vehicle)
3. Check if tire sizes appear automatically
4. Check browser console for any errors

### Test Fallback:
1. Remove API key
2. Enter a VIN
3. System should work normally (no AI, manual entry)

---

## Troubleshooting

### AI Not Working
- Check `GEMINI_API_KEY` is set correctly in Render
- Check API key is valid (starts with `AIza...`)
- Check error logs in Render
- Verify cURL extension is enabled
- Check if you've exceeded free tier limits (15/min, 1500/day)

### Wrong Tire Sizes
- AI may occasionally return incorrect sizes
- Always show verification message to users
- Users can edit AI-detected sizes before submitting

### Slow Response
- AI API calls take 1-2 seconds
- Consider caching results (optional)
- Show loading indicator during AI call

---

## Future Enhancements

- [x] Use FREE AI provider (Google Gemini)
- [ ] Cache AI results to reduce API calls
- [ ] Add confidence scores for AI predictions
- [ ] Allow users to rate AI accuracy
- [ ] Batch processing for multiple vehicles

---

## Quick Setup (2 Minutes)

1. **Get FREE API Key:** https://makersuite.google.com/app/apikey
2. **Add to Render:** `GEMINI_API_KEY` = `AIza...`
3. **Done!** AI tire detection works automatically - **100% FREE!**

---

**The FREE AI tire size detection is now integrated!** 🚀

No credit card needed. Just add your free Google Gemini API key and it works!
