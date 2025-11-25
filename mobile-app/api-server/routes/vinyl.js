const express = require('express');
const db = require('../config/database');
const authMiddleware = require('../middleware/auth');

const router = express.Router();

// Helper to generate ID
const generateId = (artist, title) => {
    return Buffer.from(`${artist}|${title}`).toString('base64');
};

// Helper to parse ID
const parseId = (id) => {
    const decoded = Buffer.from(id, 'base64').toString('utf8');
    const parts = decoded.split('|');
    if (parts.length < 2) return null;
    return { artist: parts[0], title: parts[1] };
};

// Get all vinyls with optional filters
router.get('/', authMiddleware, async (req, res) => {
    try {
        const { search, artist, genre, year } = req.query;

        let query = 'SELECT * FROM vinili WHERE 1=1';
        const params = [];

        // Add search filter
        if (search) {
            query += ' AND (Artista LIKE ? OR Titolo LIKE ?)';
            params.push(`%${search}%`, `%${search}%`);
        }

        // Add artist filter
        if (artist) {
            query += ' AND Artista = ?';
            params.push(artist);
        }

        // Add genre filter
        if (genre) {
            query += ' AND Genere = ?';
            params.push(genre);
        }

        // Add year filter
        if (year) {
            query += ' AND Anno = ?';
            params.push(year);
        }

        query += ' ORDER BY Artista, Titolo';

        const [rows] = await db.query(query, params);

        // Add virtual ID to each row
        const data = rows.map(row => ({
            ...row,
            id: generateId(row.Artista, row.Titolo)
        }));

        res.json({
            success: true,
            data: data,
            count: data.length
        });

    } catch (error) {
        console.error('Get vinyls error:', error);
        res.status(500).json({
            success: false,
            error: 'Error fetching vinyls'
        });
    }
});

// Get single vinyl by ID
router.get('/:id', authMiddleware, async (req, res) => {
    try {
        const { id } = req.params;
        const parsed = parseId(id);

        if (!parsed) {
            return res.status(400).json({
                success: false,
                error: 'Invalid ID format'
            });
        }

        const [rows] = await db.query(
            'SELECT * FROM vinili WHERE Artista = ? AND Titolo = ?',
            [parsed.artist, parsed.title]
        );

        if (rows.length === 0) {
            return res.status(404).json({
                success: false,
                error: 'Vinyl not found'
            });
        }

        const vinyl = {
            ...rows[0],
            id: id
        };

        res.json({
            success: true,
            data: vinyl
        });

    } catch (error) {
        console.error('Get vinyl error:', error);
        res.status(500).json({
            success: false,
            error: 'Error fetching vinyl'
        });
    }
});

// Add new vinyl
router.post('/', authMiddleware, async (req, res) => {
    try {
        const { artist, title, year, genre, support } = req.body;

        if (!artist || !title) {
            return res.status(400).json({
                success: false,
                error: 'Artist and title are required'
            });
        }

        const [result] = await db.query(
            'INSERT INTO vinili (Artista, Titolo, Anno, Genere, Supporto) VALUES (?, ?, ?, ?, ?)',
            [artist, title, year || '', genre || '', support || 'vinyl']
        );

        const newId = generateId(artist, title);

        res.status(201).json({
            success: true,
            data: {
                id: newId,
                artist,
                title,
                year,
                genre,
                support
            },
            message: 'Vinyl added successfully'
        });

    } catch (error) {
        console.error('Add vinyl error:', error);
        res.status(500).json({
            success: false,
            error: 'Error adding vinyl'
        });
    }
});

// Update vinyl
router.put('/:id', authMiddleware, async (req, res) => {
    try {
        const { id } = req.params;
        const parsed = parseId(id);
        const { artist, title, year, genre, support } = req.body;

        if (!parsed) {
            return res.status(400).json({
                success: false,
                error: 'Invalid ID format'
            });
        }

        if (!artist || !title) {
            return res.status(400).json({
                success: false,
                error: 'Artist and title are required'
            });
        }

        const [result] = await db.query(
            'UPDATE vinili SET Artista = ?, Titolo = ?, Anno = ?, Genere = ?, Supporto = ? WHERE Artista = ? AND Titolo = ?',
            [artist, title, year || '', genre || '', support || 'vinyl', parsed.artist, parsed.title]
        );

        if (result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                error: 'Vinyl not found'
            });
        }

        const newId = generateId(artist, title);

        res.json({
            success: true,
            data: {
                id: newId,
                artist,
                title,
                year,
                genre,
                support
            },
            message: 'Vinyl updated successfully'
        });

    } catch (error) {
        console.error('Update vinyl error:', error);
        res.status(500).json({
            success: false,
            error: 'Error updating vinyl'
        });
    }
});

// Delete vinyl
router.delete('/:id', authMiddleware, async (req, res) => {
    try {
        const { id } = req.params;
        const parsed = parseId(id);

        if (!parsed) {
            return res.status(400).json({
                success: false,
                error: 'Invalid ID format'
            });
        }

        const [result] = await db.query(
            'DELETE FROM vinili WHERE Artista = ? AND Titolo = ?',
            [parsed.artist, parsed.title]
        );

        if (result.affectedRows === 0) {
            return res.status(404).json({
                success: false,
                error: 'Vinyl not found'
            });
        }

        res.json({
            success: true,
            message: 'Vinyl deleted successfully'
        });

    } catch (error) {
        console.error('Delete vinyl error:', error);
        res.status(500).json({
            success: false,
            error: 'Error deleting vinyl'
        });
    }
});

// Get distinct artists
router.get('/filters/artists', authMiddleware, async (req, res) => {
    try {
        const [rows] = await db.query(
            'SELECT DISTINCT Artista FROM vinili ORDER BY Artista'
        );

        res.json({
            success: true,
            data: rows.map(row => row.Artista)
        });

    } catch (error) {
        console.error('Get artists error:', error);
        res.status(500).json({
            success: false,
            error: 'Error fetching artists'
        });
    }
});

// Get distinct genres
router.get('/filters/genres', authMiddleware, async (req, res) => {
    try {
        const [rows] = await db.query(
            'SELECT DISTINCT Genere FROM vinili WHERE Genere IS NOT NULL AND Genere != "" ORDER BY Genere'
        );

        res.json({
            success: true,
            data: rows.map(row => row.Genere)
        });

    } catch (error) {
        console.error('Get genres error:', error);
        res.status(500).json({
            success: false,
            error: 'Error fetching genres'
        });
    }
});

// Get distinct years
router.get('/filters/years', authMiddleware, async (req, res) => {
    try {
        const [rows] = await db.query(
            'SELECT DISTINCT Anno FROM vinili WHERE Anno IS NOT NULL AND Anno != "" ORDER BY Anno DESC'
        );

        res.json({
            success: true,
            data: rows.map(row => row.Anno)
        });

    } catch (error) {
        console.error('Get years error:', error);
        res.status(500).json({
            success: false,
            error: 'Error fetching years'
        });
    }
});


// Debug route to check table schema
router.get('/debug/schema', async (req, res) => {
    try {
        const [rows] = await db.query('DESCRIBE vinili');
        res.json(rows);
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

module.exports = router;
