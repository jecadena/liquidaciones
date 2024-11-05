const express = require('express');
const mysql = require('mysql');
const cors = require('cors');
const app = express();
const port = 3000;

app.use(cors());
app.use(express.json());

const connection = mysql.createConnection({
  host: 'SERVER10\SQL2008',
  database: 'bd_AgViajes',
  user: 'sa',
  password: '12345678'
});

connection.connect((err) => {
  if (err) {
    console.error('Error al conectar a la base de datos:', err);
    return;
  }
  console.log('Conexión a la base de datos establecida');
});

app.post('/registro/procesarExcel', (req, res) => {
  const excelData = req.body;
  
  if (!Array.isArray(excelData)) {
    return res.status(400).json({ error: 'Los datos del Excel no están en el formato correcto' });
  }

  let errorSent = false;

  let completedQueries = 0;

  excelData.forEach(row => {
    const cp = row[0];
    const tip_doc = row[1];
    const serie = row[2];
    const docu = row[3];

    connection.query('SELECT * FROM documento WHERE cp = ? AND tip_doc = ? AND serie = ? AND docu = ? AND liquidacion IS NULL', 
      [cp, tip_doc, serie, docu], 
      (error, results) => {
        if (error) {
          console.error('Error al realizar la consulta:', error);
          if (!errorSent) {
            errorSent = true;
            res.status(500).json({ error: 'Error al consultar la base de datos' });
          }
          return;
        }

        if (results.length === 0) {
          if (!errorSent) {
            errorSent = true;
            res.status(400).json({ message: 'La liquidación ya existe para estos valores' });
          }
        } else {
          const maxLiquidacion = Math.max(...results.map(result => result.liquidacion)); 
          const nuevaLiquidacion = maxLiquidacion + 1; 
          
          connection.query('UPDATE documento SET liquidacion = ? WHERE cp = ? AND tip_doc = ? AND serie = ? AND docu = ? AND liquidacion IS NULL', 
            [nuevaLiquidacion, cp, tip_doc, serie, docu], 
            (error, results) => {
              if (error) {
                console.error('Error al actualizar la base de datos:', error);
                if (!errorSent) {
                  errorSent = true;
                  res.status(500).json({ error: 'Error al actualizar la base de datos' });
                }
                return;
              }
              console.log('Filas actualizadas correctamente');

              completedQueries++;

              if (completedQueries === excelData.length) {
                res.json({ message: 'Datos del Excel procesados correctamente' });
              }
            });
        }
    });
  });
});

app.listen(port, () => {
  console.log(`Servidor iniciado en http://localhost:${port}`);
});
