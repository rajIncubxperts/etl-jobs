const mysql = require('mysql2');

const sourceDBConfig = {
  host: '127.0.0.1',
  user: 'root',
  password: 'CraajDec@2022%',
  database: 'source_database'
};

const destDBConfig = {
  host: '127.0.0.1',
  user: 'root',
  password: 'CraajDec@2022%',
  database: 'destination_database'
};

// Create MySQL connections
const sourceConnection = mysql.createConnection(sourceDBConfig);
const destConnection = mysql.createConnection(destDBConfig);

sourceConnection.connect((err) => {
    if (err) throw err;
    console.log('Connected to source_database!');
  
    // After connecting to the source database, create the 'user' table in the destination database
    const createTableQuery = `
      CREATE TABLE IF NOT EXISTS user (
        id INT PRIMARY KEY,
        username VARCHAR(255),
        email VARCHAR(255),
        address VARCHAR(255),
        profile_info TEXT
      )
    `;
  
    destConnection.query(createTableQuery, (err) => {
      if (err) throw err;
      console.log('Created user table in destination_database!');
  
      // Proceed with data extraction and insertion
      sourceConnection.query('SELECT * FROM user', (err, rows) => {
        if (err) throw err;
  
        rows.forEach((user) => {
          const { id, username, email, address, profile_info } = user;
          const insertQuery = `INSERT INTO user (id, username, email, address, profile_info) VALUES (${id}, '${username}', '${email}', '${address}', '${profile_info}')`;
  
          destConnection.query(insertQuery, (err) => {
            if (err) throw err;
            console.log(`Inserted user with ID ${id} into destination_database`);
          });
        });
        sourceConnection.end();
        destConnection.end();
      });
    });
  });
  