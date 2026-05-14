# Groq AI Chat Setup Guide

## Current Issue

Your API key doesn't have access to available Groq models. This typically means:

1. The API key needs to be regenerated
2. The key doesn't have the right permissions
3. Your Groq account may need activation

## Steps to Fix

### 1. Regenerate Your API Key

⚠️ **IMPORTANT:** You exposed your previous API key in this conversation. Follow these steps:

1. Visit [Groq Console](https://console.groq.com/keys)
2. Log in to your account
3. Find your API key section
4. **Delete** the old key
5. **Create a new API key**
6. Copy the new key

### 2. Update Your .env File

1. Open `e:\WCT\Final\Reread\.env`
2. Replace the old key with your new key:
   ```
   GROQ_API_KEY=your_new_key_here
   ```
3. **Save the file**

### 3. Check Available Models

Go to [Groq Console - Models](https://console.groq.com/docs/models) and note which models are available for your account.

Common current models:
- `mixtral-8x7b-32768`
- `llama2-70b-4096`
- `llama3-8b-8192`
- `llama3-70b-8192`
- `gemma-7b-it`

### 4. Test the Connection

Visit: `http://localhost:8000/test_groq.php`

This will show:
- ✅ HTTP 200 = Success
- ❌ HTTP 400/401 = Key issue or model not available
- ❌ HTTP 404 = Model not available for your key

### 5. Update Model Name (if needed)

If the available model is different, update the model name in:
- `includes/groq_chat.php` (line 57)
- `test_groq.php` (line 36)

Replace `'model' => 'gemma-7b-it',` with your available model.

---

## Testing

After setup, go to `http://localhost:8000` and click the purple chat button in the bottom-right corner.

## Troubleshooting

- **"Connection error"**: Network issue or timeout
- **"API key is invalid"**: Generate a new key from console
- **"Model not found"**: The model isn't available for your key
- **No response**: Check that your API key is correctly set in `.env`

Need help? Check your console logs at `http://localhost:8000/test_groq.php`
