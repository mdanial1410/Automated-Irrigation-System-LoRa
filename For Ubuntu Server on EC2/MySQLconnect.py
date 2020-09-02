import pymysql
from contextlib import closing

class MySQL_connect(object):
    def __init__(self):
        self.host = "localhost"
        self.user = "lora"
        self.pw = "lora123"
        self.database = "fyp_lora"

    def query_db(self):
        db = pymysql.connect(host=self.host,
                             user=self.user,
                             password=self.pw,
                             db=self.database)

        with closing(db.cursor()) as cursor:
            sql = "SELECT * FROM actuator"
            # execute the sql command
            cursor.execute(sql)
            result = cursor.fetchall()
            for x in result:
                print(x)
            return result

        db.close()

    def insert_db(self, sensor, value, dateTime):
    # def insert_db(self, sensor, value):
        db = pymysql.connect(host=self.host,
                             user=self.user,
                             password=self.pw,
                             db=self.database)

        #  'With' will take care about closing cursor (it's important because
        #  it's unmanaged resource) automatically. The benefit is it will close
        #  cursor in case of exception too.

        with closing(db.cursor()) as cursor:
            sensor = str(sensor).upper()
            ## with date time
            sql = "INSERT INTO sensor (sensorName,sensorValue,Date) VALUES (%s,%s,%s)"
            val = (sensor,value,dateTime)

            # ## without date time
            # sql = "INSERT INTO sensor (sensorName,sensorValue) VALUES (%s,%s)"
            # val = (sensor,value)

            # execute the sql command
            cursor.execute(sql, val)
            # commit the changes inside the table
            db.commit()

        db.close()
