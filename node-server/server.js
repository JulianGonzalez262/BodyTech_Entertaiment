const express = require('express');
const cors = require('cors');
const pool = require('./config/database');

const app = express();
app.use(cors({
    origin: 'http://localhost'
}));
const PORT = 3000;

app.get('/', (req, res) => {
    res.json({
        mensaje: 'Servidor Node.js de BodyTech funcionando correctamente'
    });
});

app.get('/test-db', async (req, res) => {
    try {
        const [resultado] = await pool.query('SELECT 1 AS conexion');

        res.json({
            estado: 'OK',
            mensaje: 'Node.js se conectó correctamente a MySQL',
            resultado: resultado
        });

    } catch (error) {
        console.error('Error de conexión MySQL:', error.message);

        res.status(500).json({
            estado: 'ERROR',
            mensaje: 'No se pudo conectar con MySQL'
        });
    }
});

app.get('/api/productos', async (req, res) => {
    try {
        const [productos] = await pool.query(`
            SELECT
                id,
                nombre,
                descripcion,
                precio,
                stock,
                categoria
            FROM productos
            WHERE activo = 1
            ORDER BY created_at DESC
        `);

        res.json(productos);

    } catch (error) {
        console.error('Error al consultar productos:', error.message);

        res.status(500).json({
            estado: 'ERROR',
            mensaje: 'No se pudieron obtener los productos'
        });
    }
});

app.get('/api/productos/buscar', async (req, res) => {
    try {
        const busqueda = (req.query.q || '').trim();

        if (busqueda.length < 2) {
            return res.json([]);
        }

        const termino = `%${busqueda}%`;

        const [productos] = await pool.query(`
            SELECT
                p.id,
                p.nombre,
                p.descripcion,
                p.precio,
                p.stock,
                p.categoria,
                (
                    SELECT pm.media_path
                    FROM producto_media pm
                    WHERE pm.producto_id = p.id
                      AND pm.media_type = 'image'
                    LIMIT 1
                ) AS imagen
            FROM productos p
            WHERE p.activo = 1
              AND (
                    p.nombre LIKE ?
                    OR p.descripcion LIKE ?
                    OR p.categoria LIKE ?
              )
            ORDER BY p.created_at DESC
        `, [termino, termino, termino]);

        res.json(productos);

    } catch (error) {
        console.error('Error al buscar productos:', error.message);

        res.status(500).json({
            estado: 'ERROR',
            mensaje: 'No se pudieron buscar los productos'
        });
    }
});

app.get('/api/gimnasios/buscar', async (req, res) => {
    try {
        const busqueda = (req.query.q || '').trim();

        if (busqueda.length < 2) {
            return res.json([]);
        }

        const termino = `%${busqueda}%`;

        const [gimnasios] = await pool.query(`
            SELECT
                g.id,
                g.nombre,
                g.descripcion,
                g.direccion,
                g.ciudad,
                g.telefono,
                (
                    SELECT gm.media_path
                    FROM gimnasio_media gm
                    WHERE gm.gimnasio_id = g.id
                      AND gm.media_type = 'image'
                    LIMIT 1
                ) AS imagen
            FROM gimnasios g
            WHERE g.activo = 1
              AND (
                    g.nombre LIKE ?
                    OR g.descripcion LIKE ?
                    OR g.direccion LIKE ?
                    OR g.ciudad LIKE ?
                    OR g.telefono LIKE ?
              )
            ORDER BY g.created_at DESC
        `, [
            termino,
            termino,
            termino,
            termino,
            termino
        ]);

        res.json(gimnasios);

    } catch (error) {
        console.error(
            'Error al buscar gimnasios:',
            error.message
        );

        res.status(500).json({
            estado: 'ERROR',
            mensaje: 'No se pudieron buscar los gimnasios'
        });
    }
});

app.listen(PORT, () => {
    console.log(`Servidor Node.js ejecutándose en http://localhost:${PORT}`);
});