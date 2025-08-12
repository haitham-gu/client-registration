const path = require('path');
const express = require('express');

const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(express.json());

// Static files
const publicDir = path.join(__dirname, '..', 'public');
app.use(express.static(publicDir));

// API: Register
app.post('/register', async (req, res) => {
  const { fullName, phoneDZ, phoneFR, wilaya, cityFR } = req.body || {};
  if (![fullName, phoneDZ, phoneFR, wilaya, cityFR].every(Boolean)) {
    return res.status(400).json({ ok: false, error: 'Missing fields' });
  }

  // Log for now
  console.log('New registration:', { fullName, phoneDZ, phoneFR, wilaya, cityFR });

  try {
    await saveToGoogleSheets({ fullName, phoneDZ, phoneFR, wilaya, cityFR });
    res.json({ ok: true });
  } catch (e) {
    console.error('saveToGoogleSheets failed:', e);
    res.status(500).json({ ok: false, error: 'Failed to save' });
  }
});

// Placeholder persistence
async function saveToGoogleSheets(data) {
  // TODO: integrate with Google Sheets API
  return Promise.resolve();
}

// Start server
if (require.main === module) {
  app.listen(PORT, () => {
    console.log(`Server running on http://localhost:${PORT}`);
  });
}

module.exports = app;
