@Echo off
FOR /F %%i IN ('C:\VMSL\FindMac.exe') DO set _IP_MAC=%%i
rem echo %_IP_MAC%
curl https://vmsl.iem.cyut.edu.tw/remote/php/return.php?data=%_IP_MAC%