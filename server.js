const express = require('express');
const session = require('express-session');
const bodyParser = require('body-parser');
const mysql = require('mysql');
const cors = require('cors');
const jwt = require('jsonwebtoken');

const app = express();
const port = 3000;

app.use(session({
  secret: '1413003141',
  resave: false,
  saveUninitialized: true
}));

app.use(bodyParser.json());

const connection = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: '',
  database: 'gds'
});

connection.connect();

app.use(cors());

app.post('/login', (req, res) => {
  const { usuario, contrasena } = req.body;
  connection.query('SELECT id, rolUsuario, nombre, fechaIngreso FROM usuariosgds WHERE usuario = ? AND clave = ?', [usuario, contrasena], (error, results) => {
    if (error) {
      console.error(error);
      return res.status(500).json({ error: 'Error al intentar iniciar sesión' });
    }
    if (results.length === 1) {
      const userData = results[0];
      const token = jwt.sign({ username: usuario, userData }, 'clave_secreta', { expiresIn: '1h' });
      req.session.username = usuario;
      req.session.userData = userData;
      const idUsuario = userData.id;
      req.session.idusuario = idUsuario;
      const rolUsuario = userData.rolUsuario;
      req.session.rolUsuario = rolUsuario;
      const fechaIngreso = new Date().toISOString().slice(0, 19).replace('T', ' ');
      connection.query('INSERT INTO registrousuarios (idUsuario, fechaIngreso) VALUES (?, ?)', [idUsuario, fechaIngreso], (error, results) => {
        if (error) {
          console.error(error);
          return res.status(500).json({ error: 'Error al guardar el registro de usuario' });
        }
        console.log('Registro de usuario guardado exitosamente');
      });

      req.session.idUsuario = userData.id;
      req.session.rolUsuario = userData.rolUsuario;
      req.session.nombre = userData.nombre;
      req.session.fechaIngreso = userData.fechaIngreso;

      return res.json({ token, userData });
    } else {
      return res.status(401).json({ error: 'Credenciales incorrectas' });
    }
  });
});


function verificarToken(req, res, next) {
  const token = req.headers.authorization && req.headers.authorization.split(' ')[1];
  if (!token) {
    return res.redirect('/login');
  }
  jwt.verify(token, 'clave_secreta', (err, decoded) => {
    if (err) {
      return res.redirect('/login');
    }
    req.decoded = decoded;
    req.session.timestamp = new Date().getTime();
    const newToken = jwt.sign({ username: decoded.username, userData: decoded.userData }, 'clave_secreta', { expiresIn: '1h' });
    res.set('Authorization', 'Bearer ' + newToken);
    next();
  });
}


app.get('/amadeus', verificarToken, verificarInactividad, (req, res) => {
  connection.query(`SELECT A.PnrId, A.AgencyName, A.OfficeIATA, A.IssuerCode, A.IssuedDate, COUNT(H.PnrId) AS cuenta 
  FROM amadeus A 
  LEFT JOIN hotelesamadeus H ON A.PnrId = H.PnrId 
  GROUP BY A.PnrId, A.AgencyName, A.OfficeIATA, A.IssuerCode, A.IssuedDate
  HAVING COUNT(H.PnrId) > 0
  `, (error, results) => {
    if (error) {
      console.error(error);
      return res.status(500).json({ error: 'Error al obtener los registros de la tabla amadeus' });
    }
    return res.json(results);
  });
});

app.post('/amadeus/buscar/:termino', verificarToken, verificarInactividad, (req, res) => {
  const termino = req.params.termino;
  const query = `SELECT PnrId, AgencyName, OfficeIATA, IssuerCode, IssuedDate, ItineraryDepartureDate, ItineraryArrivalDate 
                 FROM amadeus 
                 WHERE AgencyName LIKE '%${termino}%'
                 OR IssuerCode LIKE '%${termino}%' 
                 ORDER BY AgencyName ASC`;
  connection.query(query, (error, results) => {
    if (error) {
      console.error(error);
      return res.status(500).json({ error: 'No se han cargado los datos de Amadeus' });
    }
    return res.json(results);
  });
});

app.post('/amadeus/buscar-por-agencia/:nombreAgencia', verificarToken, verificarInactividad, (req, res) => {
  const nombreAgencia = req.params.nombreAgencia;
  const query = `
    SELECT PnrId, CheckInDate, CheckOutDate, ConfirmationCode, HotelChainName, HotelName, HotelPrice 
    FROM hotelesamadeus 
    WHERE PnrId = ?
    ORDER BY HotelName ASC`;
  connection.query(query, [nombreAgencia], (error, results) => {
    if (error) {
      console.error(error);
      return res.status(500).json({ error: 'No se encuentra el registro en Amadeus' });
    }
    return res.json(results);
  });
});

app.get('/comisiones', verificarToken, verificarInactividad, (req, res) => {
  const idUsuario = req.query.idUsuario;
  const rolUsuario = req.query.rolUsuario;
  let query = 'SELECT h.*, ABS(DATEDIFF(h.CheckOutDate, h.CheckInDate)) AS numeroDias,(ABS(DATEDIFF(h.CheckOutDate, h.CheckInDate)) * h.HotelPrice) AS sumaParcial, a.* FROM hotelesamadeus AS h INNER JOIN amadeus AS a ON h.PnrId = a.PnrId';
  if (rolUsuario !== '1') {
    query += ` WHERE h.idUsuario = ${idUsuario} ORDER BY h.HotelChainName ASC`;
  }
  connection.query(query, (error, results) => {
    if (error) {
      console.error(error);
      return res.status(500).json({ error: 'Error en la base de datos. Consulte al administrador.', details: error });
    }
    return res.json(results);
  });
});

app.get('/comisionesc', verificarToken, verificarInactividad, (req, res) => {
  const idUsuario = req.query.idUsuario;
  const rolUsuario = req.query.rolUsuario;
  const tipoComision = "COB";
  let query = `SELECT h.*, ABS(DATEDIFF(h.CheckOutDate, h.CheckInDate)) 
              AS numeroDias,(ABS(DATEDIFF(h.CheckOutDate, h.CheckInDate)) * h.HotelPrice) AS sumaParcial, a.* 
              FROM hotelesamadeus AS h INNER JOIN amadeus AS a ON h.PnrId = a.PnrId WHERE h.estado = '${tipoComision}'`;
  if (rolUsuario !== '1') {
    query += ` AND h.idUsuario = ${idUsuario}`;
  }
  connection.query(query, (error, results) => {
    if (error) {
      console.error(error);
      return res.status(500).json({ error: 'Error en la base de datos. Consulte al administrador.', details: error });
    }
    return res.json(results);
  });
});

app.get('/comisiones/:id', verificarToken, verificarInactividad, (req, res) => {
  const id = req.params.id;
  connection.query('SELECT * FROM hotelesamadeus WHERE Id = ?', [id], (error, results) => {
    if (error) {
      console.error(error);
      return res.status(500).json({ error: 'Error al obtener los detalles de la comisión' });
    }
    if (results.length === 1) {
      return res.json(results[0]);
    } else {
      return res.status(404).json({ error: 'Comisión no encontrada' });
    }
  });
});

app.post('/comisiones/actualizar', verificarToken, verificarInactividad, (req, res) => {
  const comision = req.body;
  const id = comision.Id;
  
  connection.query('UPDATE hotelesamadeus SET ? WHERE Id = ?', [comision, id], (error, results) => {
    if (error) {
      console.error(error);
      return res.status(500).json({ error: 'Error al actualizar la comisión en la base de datos' });
    }
    return res.json({ message: 'Comisión actualizada exitosamente' });
  });
});

app.post('/comisiones/guardar', verificarToken, verificarInactividad, (req, res) => {
  const comision = req.body;
  const token = req.headers.authorization && req.headers.authorization.split(' ')[1];
  const decodedToken = jwt.verify(token, 'clave_secreta');
  const usuario = decodedToken.username;
  const idUsuario = decodedToken.userData.id;
  comision.idUsuario = idUsuario;

  if (!comision.PnrId || !comision.CheckInDate || !comision.CheckOutDate || !comision.ConfirmationCode || !comision.HotelChainName || !comision.HotelName || !comision.HotelPrice || !comision.PorComision) {
    return res.status(400).json({ error: 'Todos los campos son requeridos para guardar la comisión' });
  }

  const diferenciaDias = Math.abs(new Date(comision.CheckOutDate) - new Date(comision.CheckInDate)) / (1000 * 60 * 60 * 24);
  const total = diferenciaDias * comision.HotelPrice;
  const comisionTotal = (comision.PorComision / 100) * total;

  connection.query(
    'INSERT INTO hotelesamadeus (PnrId, CheckInDate, CheckOutDate, ConfirmationCode, HotelChainName, HotelName, HotelPrice, PorComision, numeroDias, total, comisionTotal, idUsuario, fechaCreacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())', 
    [comision.PnrId, comision.CheckInDate, comision.CheckOutDate, comision.ConfirmationCode, comision.HotelChainName, comision.HotelName, comision.HotelPrice, comision.PorComision, diferenciaDias, total, comisionTotal, comision.idUsuario], 
    (error, results) => {
      if (error) {
        console.error(error);
        return res.status(500).json({ error: 'Error al guardar la comisión en la base de datos' });
      }
      return res.json({ message: 'Comisión guardada exitosamente' });
  });
});

app.delete('/comisiones/eliminar/:id', verificarToken, verificarInactividad, (req, res) => {
  const id = req.params.id;
  connection.query('DELETE FROM hotelesamadeus WHERE Id = ?', [id], (error, results) => {
    if (error) {
      console.error(error);
      return res.status(500).json({ error: 'Error al eliminar la comisión en la base de datos' });
    }
    return res.json({ message: 'Comisión eliminada exitosamente' });
  });
});

app.post('/comisiones/buscar/:termino', verificarToken, verificarInactividad, (req, res) => {
  const termino = req.params.termino;
  const query = `
    SELECT h.*, ABS(DATEDIFF(h.CheckOutDate, h.CheckInDate)) AS numeroDias, 
           (ABS(DATEDIFF(h.CheckOutDate, h.CheckInDate)) * h.HotelPrice) AS sumaParcial, 
           a.* 
    FROM hotelesamadeus AS h 
    INNER JOIN amadeus AS a ON h.PnrId = a.PnrId 
    WHERE h.HotelName LIKE '%${termino}%' OR h.HotelChainName LIKE '%${termino}%' 
    ORDER BY h.HotelName ASC`;
  
  connection.query(query, (error, results) => {
    if (error) {
      console.error(error);
      return res.status(500).json({ error: 'Error al buscar en la tabla de comisiones' });
    }
    return res.json(results);
  });
});

app.get('/pnrs', verificarToken, verificarInactividad, (req, res) => {
  connection.query('SELECT PnrId FROM amadeus', (error, results) => {
    if (error) {
      console.error(error);
      return res.status(500).json({ error: 'Error al obtener los registros de la tabla amadeus' });
    }
    return res.json(results);
  });
});

app.get('/cadenas-hoteles', verificarToken, verificarInactividad, (req, res) => {
  connection.query('SELECT DISTINCT HotelChainName FROM hotelesamadeus', (error, results) => {
    if (error) {
      console.error(error);
      return res.status(500).json({ error: 'Error al obtener las cadenas de hoteles' });
    }
    return res.json(results);
  });
});

app.get('/agencias', verificarToken, verificarInactividad, (req, res) => {
  connection.query('SELECT DISTINCT AgencyName FROM amadeus ORDER BY AgencyName ASC', (error, results) => {
    if (error) {
      console.error(error);
      return res.status(500).json({ error: 'Error al obtener las cadenas de hoteles' });
    }
    return res.json(results);
  });
});

app.get('/hoteles', verificarToken, verificarInactividad, (req, res) => {
  connection.query('SELECT DISTINCT HotelName FROM hotelesamadeus', (error, results) => {
    if (error) {
      console.error(error);
      return res.status(500).json({ error: 'Error al obtener las cadenas de hoteles' });
    }
    return res.json(results);
  });
});

app.post('/guardar-comision', verificarToken, verificarInactividad, (req, res) => {
  const comision = req.body;
});

/*function verificarInactividad(req, res, next) {
  const tiempoInactivo = 60 * 60 * 1000; 
  const tiempoActual = new Date().getTime();
  const tiempoUltimaActividad = req.session.timestamp || 0;
  if (tiempoActual - tiempoUltimaActividad > tiempoInactivo) {
    req.session.destroy(); 
    return res.redirect('/login');
  }
  next();
}

app.use((req, res, next) => {
  const path = req.path;
  const isLoginPath = path === '/login';
  if (!req.session.username && !isLoginPath) {
    return res.redirect('/login');
  }
  next();
});*/

function verificarInactividad(req, res, next) {
  const tiempoInactivo = 60 * 60 * 1000; 
  const tiempoActual = new Date().getTime();
  const tiempoUltimaActividad = req.session.timestamp || 0;
  if (tiempoActual - tiempoUltimaActividad > tiempoInactivo) {
    req.session.destroy(); 
    return res.status(401).json({ error: 'Sesión expirada' });
  }
  next();
}

app.use((req, res, next) => {
  const path = req.path;
  const isLoginPath = path === '/login';
  if (!req.session.username && !isLoginPath) {
    return res.status(401).json({ error: 'No autorizado' });
  }
  next();
});


app.get('/usuario', verificarToken, verificarInactividad, (req, res) => {
  const username = req.username;
  connection.query('SELECT nombre, fechaIngreso FROM usuariosgds WHERE usuario = ?', [username], (error, results) => {
    if (error) {
      console.error(error);
      return res.status(500).json({ error: 'Error al obtener datos de usuario' });
    }
    if (results.length === 1) {
      return res.json(results[0]);
    } else {
      const userData = req.session.userData;
      if (!userData) {
        return res.status(404).json({ error: 'Datos de usuario no encontrados en la sesión' });
      }
      return res.json({ nombre: userData.nombre, fechaIngreso: userData.fechaIngreso });
    }
  });
});

app.post('/logout', (req, res) => {
  req.session.destroy();
  res.redirect('/login');
});

app.listen(port, () => {
  console.log(`Servidor iniciado en http://localhost:${port}`);
});