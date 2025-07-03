Reference BDO Bank Queueing System

Users: 
Admin
Controller

Admin(Contents)
 -Dashboard
 -Users
     - CRUD users
     - Assign users 
 -Counters
     *Fields
        - Counter Name
        - Counter Number 
 -Transactions
      *Table(filter date)
        -Counters, Day, Month, Total
 -Video
    - Current Video
    - Add Video
Display Window
 - Grid View Counters with Video
 
 Controller User(Contents)
 -Control Window
 -Control Panel

Synchronize  Queueing System Comprehensive Professional
color:
#303C54 and white

Components on each user folder must put the header and footer on that location

with
Bootstrap
Modal - SweetAlert2

Screens:
* login.php
  -Multi Users where Admin and Controller

Admin
*admin/index.php
   - Total Users
   - Total Counters
   - Total Transactions(in all counters)

*admin/transactions.php
   - Table of All Transactions in all Counters through Que Numbers
      Columns: Counters, This Day, This Month, Total Que Numbers

*admin/users.php
   - Table CRUD for where User_Lvl = 1(Controller users)
      Columns: Fullname, Date Created, Counter
      Create Input Fields : Firstname, Lastname, Password, Counter(combo box fetch via Counters)
      Update Fields: Counter
      Delete: via User_ID

*admin/counters.php
   - Table CRUD 
      Columns: Counter_ID, Counter Name, Counter_CurrentNumber
      Create Input Fields: Counter Name, Counter Que Number (set as optional)
      Update:
*admin/video.php
   -Upload
   -Current Video Location

Controller Users
*controller/index.php
   -Counter Name
   -Upcoming Que Number(via Awaiting)Current Que Number, Previous Que Number 
    - Set Current Number
    - Buttons: Next Number, Repeat Number, Go On Break
    - awaiting number that Clients clicked

Display Window
QueDisplay.php
  - Different Counter with Que number below
  - Video on a center similar to other queueing display system

Index Window
*index.php(where all clients can select any transactions then he get a number after done transactions and automatic print the number if printer is available if not then he can take a pic with that)


Existing Database
Database: que_db

Tables:
  *users
       User_ID
       Username
       Password
       User_Lvl  (0 admin -  1 - controller)
       User_Created
       Counter_ID
  *counters
        Counter_ID
        Counter_Name
        Counter_CurrentNumber
   *Transactions
        Transaction_ID
        Transaction_Date
        User_ID
        Counter_ID
    *Video
        Video_ID
        Video_Location


