<template>
  <form class="login-form" method="POST">
    <div class="check_time" @click="check_time()">
      <i class="fas fa-clock"></i>
    </div>
    <h2>朝陽工管遠端登入</h2>
    <div class="logout" @click="logout()">
      <i class="fas fa-sign-out-alt"></i>
    </div>
    <div class="user">
      <div class="username">
        <i class="fas fa-user-circle"></i> 姓名: {{ name }}
        <span class="edit" @click="edit()"><i class="fas fa-edit"></i></span>
      </div>
      <div class="status">
        <i class="fas fa-exclamation-circle"></i> 狀態:
        <span v-if="status"><i class="green fas fa-circle"></i> active </span>
        <span v-else="status"
          ><i class="gray fas fa-circle"></i> inactive
        </span>
      </div>
      <div v-if="status" class="downloads_btn" @click="downloads_btn()">
        Downloads
      </div>
      <div v-else="status" class="borrow_btn" @click="borrow_btn()">Borrow</div>
    </div>
  </form>
</template>

<script>
import axios from "axios";
import qs from "qs"; // 轉換格式用

export default {
  data() {
    return {
      id: "",
      name: "User",
      status: false, // F: 未租用 ， T:已租用
      ip: "",
      mac: "",
      waitting_time: 120000,
    };
  },
  created() {
    this.get_user_info();
  },
  methods: {
    // https://stackoverflow.com/questions/49661209/get-response-from-axios-with-await-async/49661388
    // 同步

    async get_user_info() {
      // console.log("updating");
      await axios({
        method: "post",
        url: "https://vmsl.iem.cyut.edu.tw/remote/php/get_session.php",
        data: {
          data: "data",
        },
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
        // }).then(function (result) { // 用這種的方式會不能在then後面使用this ，若需要用this ，需要在function中帶入參數this (var that = this , function (that) {//code } )。    https://stackoverflow.com/questions/50872310/async-await-axios-calls-with-vue-js
      }).then((result) => {
        // console.log(result.data);
        this.id = result.data.id;
        this.name = result.data.name;
        this.ip = result.data.ip;
        this.mac = result.data.mac;
        if (result.data.status == "1") {
          this.status = false; // 未租用
        } else if (result.data.status == "2") {
          this.status = true; // 已租用
        }
        // console.log("updated");
      });
    },
    async power_on() {
      await this.get_user_info();
      await axios({
        method: "post",
        url: "https://e620.iem.cyut.edu.tw/bat/power_on.php",
        data: qs.stringify({
          mac: this.mac,
        }),
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
      }).then((result) => {
        // console.log("power_on:", result.data);
      });
    },

    async searchIP() {
      await axios({
        // 這邊將已經開機的電腦 IP MAC 更新至資料庫
        method: "post",
        url: "https://e620.iem.cyut.edu.tw/bat/searchIP.php",
        data: qs.stringify({
          mac: this.mac,
        }),
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
      }).then((result5) => {
        // console.log("searchIP:", result5.data);
        if (result5.data == "1") {
          console.log("該電腦已經開機");
        } else if (result5.data == "0") {
          console.log("該電腦尚未開機");
          this.power_on(); //代表沒有搜尋到該台電腦，因此再試一次開機，這邊是以新的IP去做開機(執行完searchIP.php 就會改變mac對應的IP了)
        }
      });
    },

    async borrow_btn() {
      if (!this.status) {
        await axios({
          method: "post",
          url: "https://vmsl.iem.cyut.edu.tw/remote/php/borrow.php",
          data: qs.stringify({
            user_id: this.id,
          }),
          headers: {
            "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
          },
        }).then((result) => {
          // console.log("borrow:", result.data);
          var that = this;
          if (result.data == "2") {
            // 成功租借
            this.status = true; // 變更狀態至已租用
            this.power_on(); // 這邊因為還沒有透過searchIP.php 更新ip.mac，所以power on 不一定能成功

            setTimeout(function () {
              // 等待waitting_time 後，執行搜尋IP清單，搜尋完再開機一次
              that.searchIP(); // 先將所有已開機的電腦狀態設為1，關機的設為0，利用ipList.txt 去查看
            }, this.waitting_time);

            let timerInterval;
            this.$swal({
              icon: "success",
              title: "租借成功 !",
              html: "等待電腦開機中，於 <b></b> 秒後即可下載遠端連線檔案 ~",
              // text: "等待電腦開機中~",
              timer: this.waitting_time,
              timerProgressBar: true,
              showConfirmButton: false,
              allowOutsideClick: false,

              didOpen: () => {
                this.$swal.showLoading();
                const b = this.$swal.getHtmlContainer().querySelector("b");
                timerInterval = setInterval(() => {
                  b.textContent = parseFloat(
                    (this.$swal.getTimerLeft() / 1000).toFixed(0)
                  );
                }, 100);
              },
              willClose: () => {
                clearInterval(timerInterval);
              },
            });
            // ----------------------------------------
          } else if (result.data == "1") {
            this.$swal({
              icon: "warning",
              title: "目前租借已滿",
              text: "請稍後再試 ~",
              showConfirmButton: true,
            });
          } else if (result.data == "3") {
            this.$swal({
              icon: "warning",
              title: "目前非開放時間",
              showConfirmButton: true,
            }).then((result) => {
              if (result.isConfirmed) {
                this.check_time();
              }
            });
          } else {
            this.$swal({
              icon: "error",
              title: "租借失敗 !!",
              text: "請重新載入網頁 ~",
              timer: 1000,
              showConfirmButton: true,
              timerProgressBar: true,
            });
            setTimeout(function () {
              document.location.href =
                "https://vmsl.iem.cyut.edu.tw/remote/dist/";
            }, 1000);
          }
        });
      } else {
        this.$swal({
          icon: "error",
          title: "您已經租借過了 !!",
          text: "請重新載入網頁 !",
          timer: 1500,
          showConfirmButton: true,
          timerProgressBar: true,
        });
        setTimeout(function () {
          document.location.href = "https://vmsl.iem.cyut.edu.tw/remote/dist/";
        }, 1500);
      }
    },

    async downloads_btn() {
      // console.log("1111");
      // 更新使用者的session ，抓取MAC對應的新的IP
      await this.get_user_info(); // 必須加上 async 跟 await 才可以等待此行程式碼跑完，再繼續往下跑
      await this.searchIP();
      // console.log("2222");
      if (this.status) {
        this.$swal({
          icon: "warning",
          title: "使用規則",
          input: "checkbox",
          // html: "約在 <b></b> 秒內，即可下載遠端連線檔案 ~",
          html: "使用完畢後，請按遠端連線視窗上方之(x)關閉按鈕<br>即可完成歸還，請勿使用左下角開始功能表中的關機，<br>否則會導致下次無法使用本租借系統！",
          inputValue: 0,
          showConfirmButton: true,
          showCancelButton: true,
          inputPlaceholder: "我已經了解使用規則",
          confirmButtonText: "確定",
          cancelButtonText: "取消",
          inputValidator: (result) => {
            return !result && "必須勾選同意方塊";
          },
          allowOutsideClick: false,
        }).then((result) => {
          if (result.isConfirmed) {
            // ------------------------------------
            // let timerInterval;
            this.$swal({
              title: "請稍後",
              // html: "約在 <b></b> 秒內，即可下載遠端連線檔案 ~",
              text: "將自動下載遠端連線檔案 ~",
              showConfirmButton: false,
              timer: this.waitting_time,
              timerProgressBar: true,
              allowOutsideClick: false,
              didOpen: () => {
                this.$swal.showLoading();
                // const b = this.$swal.getHtmlContainer().querySelector("b");
                // timerInterval = setInterval(() => {
                //   b.textContent = parseFloat(
                //     (this.$swal.getTimerLeft() / 1000).toFixed(1)
                //   );
                // }, 100);
                axios({
                  method: "post",
                  url: "https://vmsl.iem.cyut.edu.tw/remote/php/check_power.php",
                  data: qs.stringify({
                    mac: this.mac,
                  }),
                  headers: {
                    "Content-Type":
                      "application/x-www-form-urlencoded; charset=UTF-8",
                  },
                }).then((result) => {
                  // console.log(result.data);
                  if (result.data == "1") {
                    this.$swal.close();
                    // 根據user去產生rdp檔案
                    document.location.href =
                      "https://vmsl.iem.cyut.edu.tw/remote/php/general_rdp.php?user_id=" +
                      this.id;

                    // 下載後，自動登出
                    axios({
                      method: "post",
                      url: "https://vmsl.iem.cyut.edu.tw/remote/php/logout.php",
                      data: {
                        data: "data",
                      },
                      headers: {
                        "Content-Type":
                          "application/x-www-form-urlencoded; charset=UTF-8",
                      },
                    }).then((result4) => {
                      // console.log(result.data);
                      if (result4.data == "1") {
                        this.$swal({
                          icon: "warning",
                          title: "5秒後將自動登出!",
                          showConfirmButton: true,
                          timer: 5000,
                          timerProgressBar: true,
                        });
                        setTimeout(function () {
                          document.location.href =
                            "https://vmsl.iem.cyut.edu.tw/remote/dist/";
                        }, 5000);
                      } else {
                        document.location.href =
                          "https://vmsl.iem.cyut.edu.tw/remote/dist/";
                      }
                    });
                  } else if (result.data == "0") {
                    this.$swal.close();
                    this.$swal({
                      icon: "warning",
                      title: "請稍後",
                      text: "電腦尚未開機完畢，導致查無租借電腦的IP ！",
                      footer: `<i class="fas fa-exclamation-triangle"></i> &nbsp&nbsp<span>若一直無法下載檔案，請重新登入後再試一次！</span>`,
                      showConfirmButton: true,
                    });
                    this.power_on(); //代表沒有搜尋到該台電腦，因此再試一次開機，這邊是以新的IP去做開機(執行完searchIP.php 就會改變mac對應的IP了)
                  }
                });
              },
              willClose: () => {
                // clearInterval(timerInterval);
              },
            }).then((result) => {
              /* Read more about handling dismissals below */
              if (result.dismiss === this.$swal.DismissReason.timer) {
                console.log("time out");
                this.$swal({
                  icon: "error",
                  title: "等候逾時 !",
                  text: "請重新點選下載按鈕",
                  showConfirmButton: true,
                });

                // 等待時間結束後，執行searchIP.php 去查看該mac是否有對應的ip，如果沒有的話，就不給他下載檔案。
              }
            });
          }
        });
      } else {
        this.$swal({
          icon: "error",
          title: "下載失敗 !!",
          text: "請重新載入網頁 !",
          showConfirmButton: true,
          timer: 1500,
          timerProgressBar: true,
        });
        setTimeout(function () {
          document.location.href = "https://vmsl.iem.cyut.edu.tw/remote/dist/";
        }, 1500);
      }
    },

    async edit() {
      console.log("22222");
      this.$swal({
        icon: "info",
        title: "變更密碼",
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText: "確定",
        cancelButtonText: "取消",
        input: "password",
        inputPlaceholder: "請輸入目前密碼",
        width: 420,
        showLoaderOnConfirm: true,
        allowOutsideClick: () => !this.$swal.isLoading(),
        preConfirm: (input_data) => {
          console.log(input_data);
          return axios(
            `https://vmsl.iem.cyut.edu.tw/remote/php/check_password.php?pass=` +
              input_data
          )
            .then((result) => {
              // console.log(result.data);
              if (result.data == "1") {
                this.$swal({
                  icon: "info",
                  title: "變更密碼",
                  showConfirmButton: true,
                  showCancelButton: true,
                  confirmButtonText: "確定",
                  cancelButtonText: "取消",
                  html: `
                    <input id="new_pass1" class="swal2-input" placeholder="請輸入新密碼" type="password" style="display: flex;transform: translateX(15px);width: 70%;">
                    <input id="new_pass2" class="swal2-input" placeholder="請再輸入一次" type="password" style="display: flex;transform: translateX(15px);width: 70%;">`,
                  width: 420,
                  showLoaderOnConfirm: true,
                  allowOutsideClick: () => !this.$swal.isLoading(),
                  preConfirm: (input) => {
                    let pass1 = document.getElementById("new_pass1").value;
                    let pass2 = document.getElementById("new_pass2").value;

                    if (pass1 == pass2) {
                      // 兩者相同才可以改密碼
                      axios({
                        method: "post",
                        url: "https://vmsl.iem.cyut.edu.tw/remote/php/modify.php",
                        data: qs.stringify({
                          pass: pass1,
                        }),
                        headers: {
                          "Content-Type":
                            "application/x-www-form-urlencoded; charset=UTF-8",
                        },
                        // }).then(function (result) { // 用這種的方式會不能在then後面使用this ，若需要用this ，需要在function中帶入參數this (var that = this , function (that) {//code } )。    https://stackoverflow.com/questions/50872310/async-await-axios-calls-with-vue-js
                      }).then((result) => {
                        // console.log(result.data);
                        if (result.data == "1") {
                          this.$swal({
                            icon: "success",
                            title: "變更成功!",
                            showConfirmButton: true,
                            timer: 5000,
                            timerProgressBar: true,
                          });
                        } else {
                          this.$swal({
                            icon: "error",
                            title: "變更失敗",
                            text: "請重新載入網頁 !",
                            showConfirmButton: true,
                            timer: 1000,
                            timerProgressBar: true,
                          });
                          setTimeout(function () {
                            document.location.href =
                              "https://vmsl.iem.cyut.edu.tw/remote/dist/";
                          }, 1000);
                        }
                      });
                    } else {
                      this.$swal.showValidationMessage(`密碼請輸入一致 !`);
                    }
                  },
                });
              } else {
                throw new Error(response.statusText);
              }
            })
            .catch((error) => {
              this.$swal.showValidationMessage(`密碼錯誤`);
            });
        },
      });
    },

    async logout() {
      this.$swal({
        icon: "warning",
        title: "確定要登出？",
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText: "確定",
        cancelButtonText: "取消",
      }).then((result) => {
        if (result.isConfirmed) {
          axios({
            method: "post",
            url: "https://vmsl.iem.cyut.edu.tw/remote/php/logout.php",
            data: {
              data: "data",
            },
            headers: {
              "Content-Type":
                "application/x-www-form-urlencoded; charset=UTF-8",
            },
          }).then((result2) => {
            // console.log(result.data);
            if (result2.data == "1") {
              this.$swal({
                icon: "warning",
                title: "登出中 ~",
                width: 280,
                toast: true,
                showConfirmButton: false,
                timer: 500,
                timerProgressBar: true,
                position: "top",
              });
              setTimeout(function () {
                document.location.href =
                  "https://vmsl.iem.cyut.edu.tw/remote/dist/";
              }, 500);
            } else {
              this.$swal({
                icon: "error",
                title: "請重新載入網頁 !",
                showConfirmButton: true,
                timer: 1000,
                timerProgressBar: true,
              });
              setTimeout(function () {
                document.location.href =
                  "https://vmsl.iem.cyut.edu.tw/remote/dist/";
              }, 1000);
            }
          });
        }
      });
    },

    async check_time() {
      // console.log("check_time");
      await axios({
        method: "post",
        url: "https://vmsl.iem.cyut.edu.tw/remote/php/check_time.php",
        data: {
          data: "data",
        },
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
        // }).then(function (result) { // 用這種的方式會不能在then後面使用this ，若需要用this ，需要在function中帶入參數this (var that = this , function (that) {//code } )。    https://stackoverflow.com/questions/50872310/async-await-axios-calls-with-vue-js
      }).then((result) => {
        console.log(result.data);
        var content = `
              <table class="table table-striped table-hover">
                <thead class="table-dark">
                  <tr>
                    <th scope="col">Day</th>
                    <th scope="col">開始</th>
                    <th scope="col">結束</th>
                  </tr>
                </thead>
                <tbody id="open_time">
        `;

        result.data.forEach(function (value, index) {
          var day = [
            "Monday",
            "Tuesday",
            "Wednesday",
            "Thursday",
            "Friday",
            "Saturday",
            "Sunday",
          ];
          // console.log(day[value.day_of_week - 1]); // index 從0開始
          content += `
            <tr>
              <td id="${value.id}">${day[value.day_of_week - 1]}</td>
              <td>${value.start}</td>
              <td>${value.end}</td>
            </tr>
          `;
        });
        content += ` </tbody>
              </table> 
          `;

        this.$swal({
          title: "開放時間",
          showConfirmButton: true,
          showCancelButton: false,
          html: content,
          confirmButtonText: "確定",
        });
      });
    },
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>
.user {
  font-weight: 600;
}

.user > div {
  margin: 10px 0;
}
.downloads_btn,
.borrow_btn {
  display: block;
  width: 100%;
  height: 50px;
  border: none;
  border-radius: 10px;
  background: #7289da;
  color: #fff;
  text-align: center;
  line-height: 50px;
  font-size: 16px;
  background-size: 200%;
  outline: none;
  cursor: pointer;
  transition: 0.5s;
  font-size: 15px;
}
.logout {
  position: fixed;
  top: 14px;
  right: 14px;
  cursor: pointer;
  color: #7289da;
}
.edit {
  cursor: pointer;
  color: #7289da;
}

.logout:hover,
.check_time:hover {
  box-shadow: 0 0 10px 10px rgba(210, 210, 210, 0.6),
    inset 0 0 10px 10px rgba(210, 210, 210, 0.6);
}
.check_time {
  position: fixed;
  top: 14px;
  left: 14px;
  cursor: pointer;
  color: #7289da;
  width: 20px;
}
.check_time {
  position: fixed;
  top: 14px;
  right: 14px;
  cursor: pointer;
  color: #7289da;
}
.login-form h2 {
  text-align: center;
  font-weight: 900;
}
#swal2-checkbox {
  -webkit-appearance: checkbox;
}
.gray {
  color: #999;
}
.green {
  color: rgb(70, 190, 54);
}
</style>