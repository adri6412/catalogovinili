const express = require('express');
const multer = require('multer');
const axios = require('axios');
const db = require('../config/database');
const authMiddleware = require('../middleware/auth');

const router = express.Router();

// Configure multer for memory storage
const upload = multer({
    storage: multer.memoryStorage(),
    limits: {
        fileSize: 10 * 1024 * 1024 // 10MB limit
    },
    fileFilter: (req, file, cb) => {
        if (file.mimetype.startsWith('image/')) {
            cb(null, true);
        } else {
            cb(new Error('Only image files are allowed'));
        }
    }
});

// AI album recognition endpoint
router.post('/recognize', authMiddleware, upload.single('image'), async (req, res) => {
    try {
        if (!req.file) {
            return res.status(400).json({
                success: false,
                error: 'No image file provided'
            });
        }

        const apiKey = process.env.OPENAI_API_KEY;

        if (!apiKey || apiKey === 'YOUR_OPENAI_API_KEY') {
            return res.status(500).json({
                success: false,
                error: 'OpenAI API key not configured'
            });
        }

        // Convert image to base64
        const base64Image = req.file.buffer.toString('base64');
        const mimeType = req.file.mimetype;

        // Prepare OpenAI API request
        const promptText = `Identify this album based on its cover art. Use your knowledge of music albums to determine the details even if the text is stylized, obscured, or hard to read.

Extract the following information in JSON format:
- artist: The name of the artist or band.
- title: The title of the album.
- year: The original release year (YYYY).
- genre: The primary genre of the album (e.g., Rock, Jazz, Pop, Electronic, Classical).

If you are not 100% sure about a specific field, make your best guess based on the visual style and context.
Return ONLY the JSON object.`;

        const openaiResponse = await axios.post(
            'https://api.openai.com/v1/chat/completions',
            {
                model: 'gpt-4o',
                messages: [
                    {
                        role: 'user',
                        content: [
                            { type: 'text', text: promptText },
                            {
                                type: 'image_url',
                                image_url: {
                                    url: `data:${mimeType};base64,${base64Image}`
                                }
                            }
                        ]
                    }
                ],
                max_tokens: 300,
                response_format: { type: 'json_object' }
            },
            {
                headers: {
                    'Authorization': `Bearer ${apiKey}`,
                    'Content-Type': 'application/json'
                },
                timeout: 30000
            }
        );

        // Parse OpenAI response
        const content = openaiResponse.data.choices[0].message.content;
        const albumData = JSON.parse(content);

        // Extract data
        const extractedData = {
            artist: albumData.artist || 'N/A',
            title: albumData.title || 'N/A',
            year: albumData.year || 'N/A',
            genre: albumData.genre || 'N/A'
        };

        res.json({
            success: true,
            data: extractedData
        });

    } catch (error) {
        console.error('AI recognition error:', error.response?.data || error.message);

        if (error.response?.status === 401) {
            return res.status(500).json({
                success: false,
                error: 'Invalid OpenAI API key'
            });
        }

        res.status(500).json({
            success: false,
            error: 'Error processing image with AI',
            details: error.message
        });
    }
});

// Save recognized album to database
router.post('/save', authMiddleware, async (req, res) => {
    try {
        const { artist, title, year, genre } = req.body;

        if (!artist || !title) {
            return res.status(400).json({
                success: false,
                error: 'Artist and title are required'
            });
        }

        const [result] = await db.query(
            'INSERT INTO vinili (Artista, Titolo, Anno, Genere, Supporto) VALUES (?, ?, ?, ?, ?)',
            [artist, title, year || '', genre || '', 'vinyl']
        );

        res.status(201).json({
            success: true,
            data: {
                id: result.insertId,
                artist,
                title,
                year,
                genre
            },
            message: 'Album saved successfully'
        });

    } catch (error) {
        console.error('Save album error:', error);
        res.status(500).json({
            success: false,
            error: 'Error saving album to database'
        });
    }
});

module.exports = router;
