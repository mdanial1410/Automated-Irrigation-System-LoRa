import socket


class socket_connect(object):
    def __init__(self):
        self.socket = None
        self.server = "SERVER_IP"
        # self.server = "192.168.1.9"
        self.port = 65533
        self.client = None
        self.addr = None

    def close_socket(self):
        if self.socket:
            self.socket.close()
            print("Closing the server socket")

    def initialise_connection(self):
        try:
            # creates the socket and bind it to the specified port
            self.socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            self.socket.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)

            print('Trying to connect to ' + str((self.server, self.port)))
            while True:
                try:
                    self.socket.connect((self.server, self.port))
                    print('Connected successfully')
                    break
                except ConnectionRefusedError:
                    # Most likely server hasn't started therefore retry.
                    continue

        except Exception as e:
            print("\nConnection Error: %s" + str(e))

    def read_data(self):
        try:
            # Receive encrypted data
            data = self.socket.recv(2048).decode('utf-8')
            if data:
                print('Received from server: ' + data)
                return data
            else:
                return

        except Exception as e:
            print("read_data exception: " + str(e))

    def write_data(self, writeData):
        try:
            # encode message to byte
            self.socket.send(writeData.encode('utf-8'))
        except Exception as e:
            print("write_data exception: " + str(e))
