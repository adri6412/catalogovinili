const express = require('express');
const axios = require('axios');
const authMiddleware = require('../middleware/auth');

const router = express.Router();

// eBay Finding API endpoint (public, no auth required)
// Query param: q (search term)
router.get('/search', async (req, res) => {
    try {
        const { q } = req.query;
        if (!q) {
            return res.status(400).json({ success: false, error: 'Missing query parameter q' });
        }
        // Simple eBay Finding API request (no auth token required for public sandbox)
        const endpoint = 'https://svcs.ebay.com/services/search/FindingService/v1';
        const params = {
            'OPERATION-NAME': 'findItemsByKeywords',
            'SERVICE-VERSION': '1.0.0',
            'SECURITY-APPNAME': process.env.EBAY_APP_ID || 'YOUR_EBAY_APP_ID',
            'RESPONSE-DATA-FORMAT': 'JSON',
            'REST-PAYLOAD': true,
            'keywords': q,
            'paginationInput.entriesPerPage': 10
        };
        const response = await axios.get(endpoint, { params });
        const items = response.data.findItemsByKeywordsResponse?.[0]?.searchResult?.[0]?.item || [];
        const results = items.map(item => ({
            title: item.title?.[0] || '',
            viewItemURL: item.viewItemURL?.[0] || '',
            galleryURL: item.galleryURL?.[0] || '',
            price: item.sellingStatus?.[0]?.currentPrice?.[0]?.__value__ || ''
        }));
        res.json({ success: true, data: results });
    } catch (error) {
        console.error('eBay search error:', error);
        res.status(500).json({ success: false, error: 'Error searching eBay' });
    }
});

module.exports = router;
