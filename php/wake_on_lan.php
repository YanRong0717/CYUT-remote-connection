<?php
class WakeOnLan
{
    private $hostname; // 喚醒設備的url地址

    private $mac; // 喚醒設備的mac地址

    private $port; // 喚醒設備的端口

    private $ip; // 喚醒設備的ip地址(不是必須的,程序會自動根據$hostname來獲取對應的ip)

    private $msg = array(

        0 => "目標機器已經是喚醒的狀態.",

        1 => "socket_create 方法執行失敗",

        2 => "socket_set_option 方法執行失敗",

        3 => "magic packet 發送成功!",

        4 => "magic packet 發送成功!",

    );

    public function __construct($hostname, $mac, $port, $ip = false)
    {
        $this->hostname = $hostname;

        $this->mac = $mac;

        $this->port = $port;

        if (!$ip) {
            $this->ip = $this->get_ip_from_hostname();

        }

    }

    public function wake_on_wan()
    {
        if ($this->is_awake()) {
            return $this->msg[0]; // 如果設備已經是喚醒的就不做其它操作了

        } else {
            $addr_byte = explode('-', $this->mac);

            $hw_addr = '';

            for ($a = 0; $a < 6; $a++) {
                $hw_addr .= chr(hexdec($addr_byte[$a]));
            }

            $msg = chr(255) . chr(255) . chr(255) . chr(255) . chr(255) . chr(255);

            for ($a = 1; $a <= 16; $a++) {
                $msg .= $hw_addr;
            }

// 通過 UDP 發送數據包

            $s = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);

            if ($s == false) {
                return $this->msg[1]; // socket_create 執行失敗

            }

            $set_opt = @socket_set_option($s, 1, 6, true);

            if ($set_opt < 0) {
                return $this->msg[2]; // socket_set_option 執行失敗
            }

            $sendto = @socket_sendto($s, $msg, strlen($msg), 0, $this->ip, $this->port);

            if ($sendto) {
                socket_close($s);

                return $this->msg[3]; // magic packet 發送成功!

            }

            return $this->msg[4]; // magic packet 發送失敗!

        }

    }

    private function is_awake()
    {
        $awake = @fsockopen($this->ip, 80, $errno, $errstr, 2);

        if ($awake) {
            fclose($awake);

        }
        return $awake;

    }

    private function get_ip_from_hostname()
    {
        return gethostbyname($this->hostname);

    }

}

// class WakeOnLan
// {

//     /**
//      * 喚醒電腦
//      * @param type $addr ,目標IP 或 廣播位址(格式 01-02-03-04-05-06 或 01:02:03:04:05:06)
//      * @param type $mac ,MAC 位址
//      * @param type $port , 7 or 9
//      * @return boolean
//      */
//     public function wake($addr, $mac, $port)
//     {
//         $mac = str_replace("-", ":", $mac);
//         $addr_byte = explode(':', $mac);
//         $hw_addr = '';
//         for ($a = 0; $a < 6; $a++) {
//             $hw_addr .= chr(hexdec($addr_byte[$a]));
//         }
//         // 開頭六個 "FF"
//         $msg = chr(255) . chr(255) . chr(255) . chr(255) . chr(255) . chr(255);
//         // 16個MAC
//         for ($a = 1; $a <= 16; $a++) {
//             $msg .= $hw_addr;
//         }
//         // 開一個 UDP 的 socket
//         // AF_INET:IP4
//         // SOCK_DGRAM：The UDP protocol is based on this socket type
//         // SOL_UDP：使用 UDP 通訊協定
//         $skt = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
//         $err = array();
//         if ($skt === false) {
//             $err[] = "Error creating socket!";
//             $err[] = "Error code is '" . socket_last_error($skt) . "' - " . socket_strerror(socket_last_error($skt));
//             throw new Exception(implode("\n", $err));
//         } else {
//             // 設定使用broadcast廣播訊息
//             // $opt_ret = socket_set_option($skt, 1, 6, TRUE);
//             $opt_ret = socket_set_option($skt, SOL_SOCKET, SO_BROADCAST, true);
//             if ($opt_ret === false) {
//                 $err[] = "setsockopt() failed, error: " . socket_strerror(socket_last_error($skt));
//                 throw new Exception(implode("\n", $err));
//             }

//             if (socket_sendto($skt, $msg, strlen($msg), 0, $addr, $port)) {
//                 //Magic Packet sent successfully
//                 $res = trim(socket_strerror(socket_last_error($skt)));
//                 socket_close($skt);
//                 return $res;
//             } else {
//                 $err[] = "Magic packet failed!";
//                 throw new Exception(implode("\n", $err));
//             }
//         }
//     }

// }
