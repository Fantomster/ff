var app = require('express')();
var server;

var sslOptions;

var server;

//local sslOptions.js:
//
//var fs = require('fs');
//var sslOptions = {
//  key: fs.readFileSync('./cert/ssl-cert-snakeoil.key'),
//  cert: fs.readFileSync('./cert/ssl-cert-snakeoil.pem')
//};
//
//module.exports = sslOptions;

try {
    sslOptions = require('./sslOptions');
    server = require('https').Server(sslOptions, app);
} catch (ex) {
    server = require('http').Server(app);
}

var io = require('socket.io')(server);
var redis = require('redis');

//local redisOptions.js:
//
//module.exports = {host: "localhost"};

var redisOptions = require('./redisOptions');

//local port.js:
//
//module.exports = 8890;

server.listen(require('./port'));

//local db.js:
//
//var mysql = require('mysql');
//
//var pool = mysql.createPool({
//    connectionLimit: 100,
//    host: 'localhost',
//    user: 'root',
//    password: 'f4simba',
//    database: 'f-keeper',
//    debug: false
//});
//
//module.exports = pool;

var pool = require('./db');

require('socketio-auth')(io, {
    authenticate: authenticate,
    postAuthenticate: postAuthenticate,
    timeout: 1000
});

function authenticate(socket, data, callback) {
    //console.log("trying to authenticate, userid:" + data.userid + ", access_token:" + data.token);
    checkUser(data, function (result) {
        return callback(null, result);
    });
}

function postAuthenticate(socket, data) {
    var redisClient = redis.createClient(redisOptions);
    redisClient.subscribe('chat');

    redisClient.on("message", function (channel, message) {
        messageObj = JSON.parse(message);
        //console.log("New message: " + message + ". In channel: " + messageObj.channel);
        socket.emit(messageObj.channel, message);
    });

    socket.on('disconnect', function () {
        redisClient.quit();
    });
}

function checkUser(data, callback) {
    pool.getConnection(function (err, connection) {
        if (err) {
            //console.log("Error in connection database");
            return callback(false);
        }

        console.log('connected as id ' + connection.threadId);

        connection.query("SELECT * FROM user WHERE (id = ?) AND (access_token = ?)", [data.userid, data.token], function (err, result) {
            connection.release();
            if (!err && (result.length > 0)) {
                //console.log("Authentication success for userid: " + data.userid);
                return callback(true);
            } else if (!err) {
                //console.log("Authentication fail for userid: " + data.userid);
                return callback(false)
            }
        });

        connection.on('error', function (err) {
            //console.log("Error in connection database");
            return callback(false);
        });

    });
}