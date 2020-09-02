from socket import *
from threading import Thread
from MySQLconnect import *
from datetime import datetime

clients = []


def clientHandler(s, c, addr):
    print(addr, "is Connected")
    msg = 'Connected to server!'
    c.send(msg.encode('utf-8'))
    while True:
        try:
            data = c.recv(2048).decode('utf-8')
            if not data:
                # This occurs if client kills connection
                print('Connection closed by client: ' + str(addr))
                clients.remove(c)
                c.close()
                return
            if data:
                if 'sensor' in data:
                    MYSQL_thread = MySQL_connect()
                    msg = list()
                    # eg: sensor 1 200 date time
                    for word in data.split():
                        msg.append(word)
                    sensorName = " ".join(msg[:2])
                    dateTime = " ".join(msg[3:])
                    MYSQL_thread.insert_db(sensorName, msg[2], dateTime)
                    # sensorName = " ".join(msg[:2])
                    # MYSQL_thread.insert_db(sensorName, msg[2])
                    print('Sensor >> ' + data)
                    broadcast_msg(s, c, 'Database updated')
                else:
                    print('Data >> ' + data)
                    broadcast_msg(s, c, data)
        except Exception as e:
            print('clientHandler error: ' + str(e))


def broadcast_msg(s, c, message):
    global clients
    if not isinstance(message, str):
        message = str(message)
    for client in clients:
        if client != s and client != c:
            # Don't send to server and source of message
            try:
                client.send(message.encode('utf-8'))
            except Exception as e:
                print('Broadcast msg error:' + str(e))
                # Connection to client terminated


def Main():
    # HOST = '192.168.1.9'
    HOST = '172.31.30.180' # ubuntu private ip
    PORT = 65532

    trds = []

    s = socket(AF_INET, SOCK_STREAM)
    s.bind((HOST, PORT))
    s.listen()

    print("Server is running on port: " + str(PORT))

    try:
        while True:
            c, addr = s.accept()
            clients.append(c)
            t = Thread(target=clientHandler, args=(s, c, addr), daemon=True)
            # setting daemon = true will let the OS handle the unused threads
            trds.append(t)
            t.start()

    except (KeyboardInterrupt, SystemExit):
        print("caught keyboard interrupt in main, exiting")
        s.close()

    except Exception as e:
        print('Socket Server error: ' + str(e))
        s.close()


if __name__ == '__main__':
    Main()