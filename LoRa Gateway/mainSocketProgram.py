import time
import threading
from socketConnect import *
from lora_gw import lora_gw
from libloragw_enums import *

solenoid_flag = False
flagIsSet = False

class Main(threading.Thread):
    gwID = '0x99'
    Freq = [867300000, 867500000, 867700000, 867900000, 868100000, 868300000, 868500000, 868700000, 868900000]
    sf = [DR.LORA_SF7, DR.LORA_SF8, DR.LORA_SF9, DR.LORA_SF10, DR.LORA_SF11, DR.LORA_SF12]
    sf_2 = {DR.UNDEFINED: 'UNDEFINED,', DR.LORA_SF7: 'SF7,', DR.LORA_SF8: 'SF8,', DR.LORA_SF9: 'SF9,', DR.LORA_SF10: 'SF10,', DR.LORA_SF11: 'SF11,', DR.LORA_SF12: 'SF12,'}

    # [nodeID, SF index, Upper Flag, Lower Flag, Frequency]
    node_sf = [['0x01', 0, 0, 0, 0],
               ['0x02', 0, 0, 0, 0],
               ['0x03', 0, 0, 0, 0],
               ['0x04', 0, 0, 0, 0],
               ['0x05', 0, 0, 0, 0],
               ['0x08', 2, 0, 0, 0]]

    def __init__(self):

        super().__init__()

        CLKSRC = 0x00
        radio_type = RADIO_TYPE.SX1257

        self.gw_thread = lora_gw(clksrc=CLKSRC, fa=self.Freq[5], fb=self.Freq[0], ft=self.Freq[8], rtype=radio_type, verbose=False)
        # self.socket_thread = socket_connect()

        # self.socket_thread.initialise_connection()
        self.gw_thread.start_gw()

    def receive_web(self):
        '''
        this is running as a thread.
        receive message from socket connection
        socket connection should send solenoid node as destination
        :return:
        '''
        while True:
            read_data = self.socket_thread.read_data()
            if read_data:
                lines = []
                for line in read_data.split(','):
                    lines.append(line)

                # for line in lines[:-1]:
                # [:-1] disregards the last element in the list
                    # self.send_lora(line)

    def send_web(self, webMSG):
        if not isinstance(webMSG, str):
            webMSG = str(webMSG)
        self.socket_thread.write_data(webMSG)

    def receive_lora(self):
        '''
        This function processes the message received from the node/nodes.
        '''
        global solenoid_flag, flagIsSet
        while True:
            try:
                pkts = self.gw_thread.radio_rx()
                if len(pkts) > 0:
                    print("Packet received!")
                    for p in pkts:
                        payloadMSG = p.strpayload
                        snr_value = p.snr
                        sf_value = self.sf_2.get(p.datarate)
                        freq = p.freq_hz
                        msg = list()
                        for value in payloadMSG.split():
                            msg.append(str(value).lower())
                        # print('Message = ' + str(msg))
                        # print('SNR = ' + str(snr_value) + ', Freq = ' + str(freq/1000000) + ' MHz' + ', SF = ' + sf_value)
                        # print()

                        # # If message is meant for gateway, process message. If not, ignore.
                        # # eg: 0x00 0x99 sensor 1 200
                        if '0x99' in msg[1]:
                            print('Message = ' + str(msg))
                            print('SNR = ' + str(snr_value) + ', Freq = ' + str(freq/1000000) + ' MHz' + ', SF = ' + sf_value)
                            print()
                            # save frequency of packet
                            for i in range(len(self.node_sf)):
                                if msg[0] in self.node_sf[i][0]:
                                    # get the index of the frequency and store in node_sf list
                                    self.node_sf[i][4] = self.Freq.index(freq)
                                    # print('Added Freq Index, updated node_sf list = ' + str(self.node_sf))
                                    break
                            
                            self.send_lora('ack', msg[0])

                            # if 'sensor' in msg[2]:
                            #     if int(msg[4]) > 500:
                            #         if not flagIsSet:
                            #             solenoid_flag = True
                            #             flagIsSet = True
                            #     else:
                            #         solenoid_flag = False

                            #     # convert msg back to string
                            #     msg = ' '.join(str(value) for value in msg[2:])
                            #     print('msg = ' + msg)
                            # #     self.send_web(msg)

                            # if solenoid_flag:
                            #     self.send_lora('solenoid on', self.node_sf[5][0])
                            
                            self.changeSFvalue(msg, snr_value)
            except Exception as e:
                print("Lora error: " + str(e))

    def send_lora(self, txMSG, dest):
        '''
        This function constructs and sends the message to the nodes.
        Controls solenoid
        '''
        global flagIsSet
        txMSG = self.gwID + ' ' + dest + ' ' + txMSG
        print('Tx msg: ' + txMSG)
        if dest in '0x08':
            flagIsSet = False
        # search through the nested list to find index for destination node
        # with the index, find the Freq and SF of the node
        for i in range(len(self.node_sf)):
            if self.node_sf[i][0] in dest:
                self.gw_thread.radio_tx(f=self.Freq[self.node_sf[i][4]], dr=self.sf[self.node_sf[i][1]], cr=CR.LORA_4_8, pl=txMSG, s=len(txMSG))
                break
        # delay after transmission
        time.sleep(1)

    def changeSFvalue(self, msg, snr_value):
        '''
        This function changes the node's transmitting power and SF value.
        The updated Tx and SF values are then updated in the node_sf array/list
        '''
        # Upper/Lower limit of node Tx power (Setting of flag)
        if 'upper' in msg:
            for i in range(len(self.node_sf)):
                if msg[0] in self.node_sf[i]:
                    self.node_sf[i][2] = 1
                    break
        if 'lower' in msg:
            for i in range(len(self.node_sf)):
                if msg[0] in self.node_sf[i]:
                    self.node_sf[i][3] = 1
                    break
        # Upper/Lower limit of node Tx power (Setting of flag)

        # Sets new SF value for each node
        if 'new' in msg and 'sf:' in msg:
            self.set_new_sf(msg[0], msg[4])
        # Sets new SF value for each node

        # fetch the flags from the nested list
        for i in range(len(self.node_sf)):
            if msg[0] in self.node_sf[i][0]:
                upper_limit = self.node_sf[i][2]
                lower_limit = self.node_sf[i][3]
                break
            else:
                upper_limit = -1
                lower_limit = -1
        # fetch the flags from the nested list

        # negative SNR means Noise is stronger 10*log(S/N)
        if snr_value < -12 and upper_limit == 0:
            # if lower limit is '1'. set it to '0' before increasing Tx power
            if lower_limit == 1:
                self.node_sf[i][3] = 0
            # pass in source ID, True - increase power
            self.change_tx_power(True, msg[0])

        elif snr_value > 6 and lower_limit == 0:
            # if upper limit is '1'. set it to '0' before decreasing Tx power
            if upper_limit == 1:
                self.node_sf[i][2] = 0
            # pass in source ID, False - decrease power
            self.change_tx_power(False, msg[0])

    def change_tx_power(self, increase, dest):
        '''
        This function tells the Node to decrease/increase its transmitting power.
        :param increase: boolean flag
        :param dest: NodeID
        :return:
        '''
        # change node tx power
        # txMSG = self.gwID + " " + dest
        if increase:
            txMSG = "increase power"
        else:
            txMSG = "decrease power"
        self.send_lora(txMSG, dest)

    def set_new_sf(self, nodeID, new_sf):
        '''
        This function searches through the nested list to find the corresponding NodeID.
        The SF value for the NodeID will then be updated.
        '''
        for i in range(len(self.node_sf)):
            if nodeID in self.node_sf[i]:
                self.node_sf[i][1] = int(new_sf) - 7
                print('node_sf list = ' + str(self.node_sf))
                break

    def initialise_threads(self):
        '''
        Start the threads
        :return:
        '''
        # threading.Timer(1, self.receive_web).start()
        # # starts web thread after 1 second
        # print("Start web thread")

        threading.Timer(1.5, self.receive_lora).start()
        print("Start lora thread")

        print("All threads initialised!")


if __name__ == "__main__":
    realRun = Main()
    realRun.initialise_threads()
