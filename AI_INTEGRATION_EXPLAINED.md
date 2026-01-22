# AI Integration Architecture - How It Works

## Overview
The system uses **Google Gemini (FREE tier)** to automatically detect tire sizes from vehicle information (Year, Make, Model, Trim).

---

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                    USER ENTERS VIN                          │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│              api/vin.php (VIN Decode API)                   │
│  1. Decode VIN using NHTSA API                              │
│  2. Get: Year, Make, Model, Trim, Body Class, Drive Type    │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│         AITireSizeService.php (AI Service)                  │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  getTireSizesFromAI()                                 │  │
│  │  Input: Year, Make, Model, Trim, Body Class          │  │
│  │  Output: { front_tire: "225/65R17", rear_tire: null }│  │
│  └──────────────────────┬───────────────────────────────┘  │
│                         │                                    │
│                         ▼                                    │
│  ┌──────────────────────────────────────────────────────┐  │
│  │  getTireSizesFromGemini()                              │  │
│  │  1. Build prompt: "What are OEM tire sizes for...?"   │  │
│  │  2. Call Google Gemini API                            │  │
│  │  3. Parse AI response (JSON)                         │  │
│  │  4. Extract tire sizes                               │  │
│  └──────────────────────┬───────────────────────────────┘  │
└──────────────────────────┼──────────────────────────────────┘
                           │
                           ▼
┌─────────────────────────────────────────────────────────────┐
│              Google Gemini API (FREE)                       │
│  URL: https://generativelanguage.googleapis.com/v1/...     │
│  Model: gemini-1.5-flash (FREE tier)                       │
│  Cost: $0.00 (FREE!)                                        │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│              Response to Frontend                           │
│  {                                                          │
│    "vehicle": { year, make, model, trim },                 │
│    "tire_sizes": {                                         │
│      "front_tire": "225/65R17",                            │
│      "rear_tire": null,                                    │
│      "source": "ai_gemini_free"                            │
│    }                                                        │
│  }                                                          │
└──────────────────────┬──────────────────────────────────────┘
                       │
                       ▼
┌─────────────────────────────────────────────────────────────┐
│         Frontend (public/assets/js/app.js)                  │
│  - Pre-fills tire size fields                              │
│  - Shows "AI Detected" badges                              │
│  - Highlights fields in green                              │
└─────────────────────────────────────────────────────────────┘
```

---

## Key Components

### 1. **AITireSizeService.php** (Main AI Service)
**Location:** `app/Services/AITireSizeService.php`

**Purpose:** Handles all AI interactions

**Key Methods:**
```php
// Main entry point
getTireSizesFromAI($year, $make, $model, $trim, $bodyClass, $driveType)
  ↓
getTireSizesFromGemini() // Calls Google Gemini API
  ↓
parseAIResponse() // Extracts tire sizes from AI response
```

**How it works:**
1. **Builds a prompt:**
   ```
   "What are the OEM (original equipment) tire sizes for a 2020 Toyota Camry LE? 
   Provide ONLY the tire sizes in standard format (e.g., 225/65R17).
   If different front and rear sizes (staggered), provide both. If same, provide only front.
   Return JSON format: {"front_tire": "225/65R17", "rear_tire": null}"
   ```

2. **Calls Gemini API:**
   - Endpoint: `https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash:generateContent`
   - Method: POST
   - Headers: API key in query parameter
   - Body: JSON with prompt

3. **Parses response:**
   - Extracts text from AI response
   - Parses JSON to get tire sizes
   - Returns: `{ front_tire: "225/65R17", rear_tire: null }`

**Fallback System:**
- Tries multiple Gemini models: `gemini-1.5-flash`, `gemini-1.5-pro`, `gemini-pro`
- Tries both `v1` and `v1beta` endpoints
- If all fail, returns `null` (graceful fallback)

---

### 2. **Integration Points**

#### A. VIN Decode API (`api/vin.php`)
```php
// After VIN is decoded
$aiTireService = new AITireSizeService();

if ($aiTireService->isAvailable()) {
    $aiTireSizes = $aiTireService->getTireSizesFromAI(
        $vehicleInfo['year'],
        $vehicleInfo['make'],
        $vehicleInfo['model'],
        $vehicleInfo['trim'] ?? null,
        $vehicleInfo['body_class'] ?? null,
        $vehicleInfo['drive_type'] ?? null
    );
    
    // Add to response
    if ($aiTireSizes) {
        $responseData['tire_sizes'] = $aiTireSizes;
    }
}
```

#### B. Dedicated AI Detection API (`api/detect-tire-sizes.php`)
**Purpose:** Allows manual AI detection when vehicle not in database

**Usage:**
```javascript
// Frontend calls this when user clicks "Detect Tire Sizes with AI"
fetch('/api/detect-tire-sizes.php', {
    method: 'POST',
    body: JSON.stringify({
        year: 2020,
        make: 'Toyota',
        model: 'Camry',
        trim: 'LE'
    })
})
```

---

### 3. **Frontend Integration** (`public/assets/js/app.js`)

**Automatic Detection (on VIN decode):**
```javascript
// When VIN is decoded
const aiTireSizes = vinData.data.tire_sizes || null;

if (aiTireSizes) {
    // Pre-fill form with AI tire sizes
    this.vehicleToAdd.front_tire = aiTireSizes.front_tire;
    this.vehicleToAdd.rear_tire = aiTireSizes.rear_tire || '';
    this.vehicleToAdd.ai_front_tire = aiTireSizes.front_tire;
    this.vehicleToAdd.ai_rear_tire = aiTireSizes.rear_tire;
}
```

**Manual Detection (button click):**
```javascript
async detectTireSizesWithAI() {
    // Call dedicated API endpoint
    const response = await fetch('/api/detect-tire-sizes.php', {
        method: 'POST',
        body: JSON.stringify({
            year: this.vehicleToAdd.year,
            make: this.vehicleToAdd.make,
            model: this.vehicleToAdd.model,
            trim: this.vehicleToAdd.trim
        })
    });
    
    const data = await response.json();
    
    // Update form fields
    if (data.success) {
        this.vehicleToAdd.front_tire = data.data.front_tire;
        this.vehicleToAdd.rear_tire = data.data.rear_tire || '';
    }
}
```

---

## Configuration

### Environment Variable
**Required:** `GEMINI_API_KEY`

**How to get:**
1. Go to: https://makersuite.google.com/app/apikey
2. Sign in with Google account (FREE)
3. Click "Create API Key"
4. Copy key (starts with `AIza...`)

**Add to Render:**
- Render Dashboard → Your Service → Environment
- Add: `GEMINI_API_KEY` = `AIza...`
- Save and redeploy

**Code reads it from:**
```php
$this->geminiKey = $_ENV['GEMINI_API_KEY'] 
    ?? $_SERVER['GEMINI_API_KEY'] 
    ?? getenv('GEMINI_API_KEY') 
    ?? null;
```

---

## AI Prompt Engineering

### The Prompt Template
```php
$prompt = "What are the OEM (original equipment) tire sizes for a {$vehicleInfo}? 
Provide ONLY the tire sizes in standard format (e.g., 225/65R17).
If different front and rear sizes (staggered), provide both. If same, provide only front.
Return JSON format: {\"front_tire\": \"225/65R17\", \"rear_tire\": null}";
```

**Why this works:**
- **Specific:** Asks for OEM (original) tire sizes
- **Format:** Specifies standard tire size format
- **Staggered:** Handles different front/rear sizes
- **JSON:** Requests structured response for easy parsing

### Example AI Response
```json
{
  "front_tire": "215/55R17",
  "rear_tire": null
}
```

Or for staggered:
```json
{
  "front_tire": "225/45R18",
  "rear_tire": "255/40R18"
}
```

---

## Error Handling & Fallbacks

### 1. **API Key Not Set**
- `isAvailable()` returns `false`
- System works normally (no AI)
- User manually enters tire sizes

### 2. **API Call Fails**
- Catches exceptions
- Logs error
- Returns `null`
- System continues without AI

### 3. **Invalid Response**
- Tries to parse JSON
- If fails, tries to extract from text
- If still fails, returns `null`

### 4. **Multiple Model Fallback**
- Tries `gemini-1.5-flash` first (fastest)
- Falls back to `gemini-1.5-pro` (more accurate)
- Falls back to `gemini-pro` (legacy)
- Tries both `v1` and `v1beta` endpoints

---

## Cost & Limits

### Google Gemini FREE Tier
- **Cost:** $0.00 (FREE!)
- **Rate Limit:** 15 requests per minute
- **Daily Limit:** 1,500 requests per day
- **No Credit Card:** Required for free tier

### Usage Example
- **100 VIN lookups/day:** FREE
- **500 VIN lookups/day:** FREE
- **1,500 VIN lookups/day:** FREE
- **Over 1,500/day:** Need paid tier (~$0.00025 per request)

---

## Security

### API Key Protection
- ✅ Stored in environment variables (never in code)
- ✅ Not committed to Git
- ✅ Only accessible server-side
- ✅ Logged partially (first 10 chars only)

### Input Validation
- ✅ All inputs sanitized
- ✅ VIN validated before AI call
- ✅ Vehicle data validated

---

## Testing

### Test AI is Working
1. Set `GEMINI_API_KEY` in Render
2. Enter any VIN
3. Check if tire sizes appear automatically
4. Check browser console for logs

### Test Fallback
1. Remove `GEMINI_API_KEY`
2. Enter VIN
3. System should work (no AI, manual entry)

### Debug Logs
Check Render logs for:
- `"Gemini API key found: AIza..."`
- `"✓ Gemini API success with model: gemini-1.5-flash"`
- `"✓ AI tire sizes SUCCESS: Front=225/65R17"`

---

## Summary

**AI Integration Flow:**
1. User enters VIN
2. System decodes VIN → Gets vehicle info
3. **AI analyzes vehicle → Detects tire sizes** (FREE!)
4. System shows tire sizes immediately
5. User verifies and continues

**Key Files:**
- `app/Services/AITireSizeService.php` - AI service
- `api/vin.php` - VIN decode (calls AI)
- `api/detect-tire-sizes.php` - Manual AI detection
- `public/assets/js/app.js` - Frontend integration

**Cost:** $0.00 (FREE tier)

**Setup Time:** 2 minutes (just add API key)
