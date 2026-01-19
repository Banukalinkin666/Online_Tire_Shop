# AI Tire Size Detection Setup Guide

This guide explains how to set up AI-powered tire size detection from VIN numbers.

---

## How It Works

### Current Flow (Without AI)
1. User enters VIN
2. System decodes VIN → Gets Year/Make/Model
3. System searches database for vehicle → Gets tire sizes
4. If vehicle not in database → User must manually enter tire sizes

### New Flow (With AI)
1. User enters VIN
2. System decodes VIN → Gets Year/Make/Model
3. **AI determines tire sizes** → Shows tire sizes immediately
4. System searches database for matching tires
5. If vehicle not in database → Pre-fills form with AI tire sizes

---

## Supported AI Providers

### 1. OpenAI (GPT-3.5/GPT-4)
- **API:** OpenAI Chat Completions API
- **Model:** gpt-3.5-turbo (default, can be changed)
- **Cost:** ~$0.001-0.002 per request
- **Speed:** Fast (~1-2 seconds)

### 2. Anthropic Claude
- **API:** Anthropic Messages API
- **Model:** claude-3-haiku-20240307
- **Cost:** ~$0.00025 per request
- **Speed:** Fast (~1-2 seconds)

---

## Setup Instructions

### Option 1: OpenAI (Recommended)

1. **Get API Key**
   - Go to https://platform.openai.com/api-keys
   - Create a new API key
   - Copy the key

2. **Add to Render Environment Variables**
   - Go to Render → Your Web Service → Environment
   - Add: `OPENAI_API_KEY` = `sk-...` (your API key)
   - Save changes

3. **Test**
   - Enter a VIN
   - System will automatically use AI to detect tire sizes
   - Tire sizes will be pre-filled in the form

### Option 2: Anthropic Claude

1. **Get API Key**
   - Go to https://console.anthropic.com/
   - Create a new API key
   - Copy the key

2. **Add to Render Environment Variables**
   - Go to Render → Your Web Service → Environment
   - Add: `ANTHROPIC_API_KEY` = `sk-ant-...` (your API key)
   - Save changes

3. **Test**
   - Enter a VIN
   - System will use Claude if OpenAI is not available

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

### OpenAI GPT-3.5-turbo
- **Per request:** ~$0.001-0.002
- **1000 requests:** ~$1-2
- **10,000 requests:** ~$10-20

### Anthropic Claude Haiku
- **Per request:** ~$0.00025
- **1000 requests:** ~$0.25
- **10,000 requests:** ~$2.50

**Recommendation:** Start with OpenAI (more reliable), or use Claude for lower costs.

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
- Check API key is set correctly
- Check API key has credits/balance
- Check error logs in Render
- Verify cURL extension is enabled

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

- [ ] Cache AI results to reduce API calls
- [ ] Add more AI providers (Google Gemini, etc.)
- [ ] Add confidence scores for AI predictions
- [ ] Allow users to rate AI accuracy
- [ ] Batch processing for multiple vehicles

---

**The AI tire size detection is now integrated!** 🚀

Just add your API key to Render environment variables and it will work automatically.
