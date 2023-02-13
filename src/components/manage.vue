<template>
  <div class="content" v-if="logined">
    <button type="button" class="btnn" @click="reset">
      <i class="fas fa-redo"></i> 初始化設定
    </button>
    <button type="button" class="btnn2" @click="add_user">
      <i class="fas fa-user-plus"></i> 新增使用者
    </button>
    <button type="button" class="btnn3" @click="import_user">
      <i class="fas fa-upload"></i> 匯入使用者
    </button>
    <button type="button" class="btnn4" @click="export_user">
      <i class="fas fa-download"></i> 匯出使用者
    </button>

    <button type="button" class="btnn5" @click="login_record">
      <i class="fas fa-file"></i>&nbsp&nbsp登入紀錄
    </button>
    <button type="button" class="btnn6" @click="borrow_record">
      <i class="fas fa-file"></i>&nbsp&nbsp租借紀錄
    </button>
    <div class="logout" @click="logout()">
      <i class="fas fa-sign-out-alt"></i>
    </div>
    <form method="POST" id="form1">
      <!-- <h2>匯入設定檔案</h2>
    <input
      type="file"
      name="file"
      class="upload"
      id="file_upload"
      accept=".csv"
      @change="onFileChange"
    /><br />
    <button type="button" class="btn" @click="submit">送出</button> -->

      <table class="table table-striped table-hover">
        <thead class="table-dark">
          <tr>
            <th scope="col">Day</th>
            <th scope="col">起始時間</th>
            <th scope="col">結束時間</th>
            <th scope="col">編輯</th>
          </tr>
        </thead>
        <tbody id="setting_table">
          <!-- <tr>
          <td>Monday</td>
          <td><input type="time" value="08:00:00" /></td>
          <td><input type="time" value="09:00:00" /></td>
        </tr> -->
        </tbody>
      </table>
    </form>
    <form method="POST" id="form2">
      <table class="table table-striped table-hover">
        <thead class="table-dark">
          <tr>
            <th scope="col">ID</th>
            <th scope="col">姓名</th>
            <th scope="col">狀態</th>
            <th scope="col">&nbsp編輯</th>
          </tr>
        </thead>
        <tbody id="user_table"></tbody>
      </table>
    </form>
  </div>
</template>

<script>
import axios from "axios";
import qs from "qs"; // 轉換格式用

let db_id = 0;

export default {
  data() {
    return {
      logined: true,
    };
  },
  mounted() {
    this.$emit("manage_mode", true);
    let that = this;
    $(document).on("click", ".edit_time", function (e) {
      let item_id = $(this)[0].id;

      db_id = $(this).parent().children(0).eq(0)[0].id; // 抓取上一層的Day id  這個是資料庫的setting's id
      let current_start = $(this).parent().children(0).eq(1)[0].innerText; // 抓取上一層的Day id  這個是資料庫的setting's id
      let current_end = $(this).parent().children(0).eq(2)[0].innerText; // 抓取上一層的Day id  這個是資料庫的setting's id
      // console.log(item_id);
      // console.log(current_start);
      // console.log(current_end);

      // console.log(db_id);
      var day = [
        "Monday",
        "Tuesday",
        "Wednesday",
        "Thursday",
        "Friday",
        "Saturday",
        "Sunday",
      ];

      that.$swal({
        title: day[item_id - 1],
        showConfirmButton: true,
        showCancelButton: true,
        allowOutsideClick: false,
        html: `
                起始時間 : <input id="start"  type="time" value=${current_start} step="1"><br><br>
                結束時間 : <input id="end"    type="time" value=${current_end} step="1">
              `,
        width: 400,
        showLoaderOnConfirm: true,
        confirmButtonText: "確定",
        cancelButtonText: "取消",
        preConfirm: (input) => {
          let start = document.getElementById("start").value;
          let end = document.getElementById("end").value;
          // console.log(start, end);
          if (start < end) {
            // console.log("11111");
            that.edit_time(start, end);
          } else {
            // console.log("2222");

            that.$swal.showValidationMessage(`"起始時間"須小於"結束時間" !`);
          }
        },
      });
    });

    $(document).on("click", ".edit_status", function (e) {
      let user_id = $(this)[0].id;
      let name = $(this).parent().parent().children(1).eq(1)[0].innerText;
      // console.log(user_id);
      // console.log($(this).parent().parent().children(1).eq(1)[0].innerText);

      that
        .$swal({
          icon: "warning",
          title: "歸還電腦",
          html: '此功能會將"' + name + '"的"租借狀態"變為"未租借"。',
          showConfirmButton: true,
          showCancelButton: true,
          confirmButtonText: "確定",
          cancelButtonText: "取消",
        })
        .then((result) => {
          if (result.isConfirmed) {
            that.edit_status(user_id);
          }
        });
    });

    $(document).on("click", ".delete_user", function (e) {
      let user_id = $(this)[0].id;
      // let name = $(this).parent().parent().children(1).eq(1)[0].innerText;
      let id = $(this).parent().parent().children(0).eq(0)[0].innerText;
      // console.log(user_id);
      // console.log(id);

      that
        .$swal({
          // icon: "warning",
          title: "刪除使用者",
          input: "checkbox",
          inputValue: 0,
          showConfirmButton: true,
          showCancelButton: true,
          inputPlaceholder: "確定要刪除 ID : " + id,
          confirmButtonText: "確定",
          cancelButtonText: "取消",
          inputValidator: (result) => {
            return !result && "必須勾選同意方塊";
          },
        })
        .then((result) => {
          if (result.isConfirmed) {
            // console.log(77777);
            that.delete_user(user_id);
          }
        });
    });
  },
  watch: {
    counter() {
      console.log(this.counter);
    },
  },

  methods: {
    async get_setting() {
      // console.log("get_setting");
      await axios({
        method: "post",
        url: "https://vmsl.iem.cyut.edu.tw/remote/php/get_setting.php",
        data: {
          data: "data",
        },
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
        // }).then(function (result) { // 用這種的方式會不能在then後面使用this ，若需要用this ，需要在function中帶入參數this (var that = this , function (that) {//code } )。    https://stackoverflow.com/questions/50872310/async-await-axios-calls-with-vue-js
      }).then((result) => {
        // console.log(result);
        // console.log(result.data[3].day_of_week);

        var setting_table = document.getElementById("setting_table");
        var content = ``;
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
              <td class="edit_time" id="${
                value.day_of_week
              }"><span >&nbsp&nbsp<i class="fas fa-edit"></i></span></td>
            </tr>
          `;
        });
        setting_table.innerHTML = "";
        setting_table.innerHTML += content;
      });
    },

    async get_user() {
      // console.log("get_setting");
      await axios({
        method: "post",
        url: "https://vmsl.iem.cyut.edu.tw/remote/php/get_user.php",
        data: {
          data: "data",
        },
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
        // }).then(function (result) { // 用這種的方式會不能在then後面使用this ，若需要用this ，需要在function中帶入參數this (var that = this , function (that) {//code } )。    https://stackoverflow.com/questions/50872310/async-await-axios-calls-with-vue-js
      }).then((result) => {
        // console.log(result);
        // console.log(result.data[3].day_of_week);

        var user_table = document.getElementById("user_table");
        var content = ``;

        result.data.forEach(function (value, index) {
          // console.log(day[value.day_of_week - 1]); // index 從0開始
          if (value.status == 0) {
            content += `
            <tr>
              <td>${value.user}</td>
              <td>${value.name}</td>
              <td>&nbsp&nbsp<i class="gray fas fa-circle"></i></td>
              <td>
              <span id="${value.id}" class="edit_status">&nbsp&nbsp<i class="fas fa-edit"></i></span>
              <span id="${value.id}" class="delete_user">&nbsp&nbsp<i class="fas fa-user-times"></i></span>
              </td>
            </tr>
          `;
          } else {
            content += `
            <tr>
              <td>${value.user}</td>
              <td>${value.name}</td>
              <td>&nbsp&nbsp<i class="green fas fa-circle"></i></td>
              <td>
              <span id="${value.id}" class="edit_status">&nbsp&nbsp<i class="fas fa-edit"></i></span>
              <span id="${value.id}" class="delete_user">&nbsp&nbsp<i class="fas fa-user-times"></i></span>
              </td>
            </tr>
          `;
          }
        });
        user_table.innerHTML = "";
        user_table.innerHTML += content;
      });
    },

    async import_user() {
      // console.log(3333);

      this.$swal({
        title: "匯入使用者",
        footer: `<i class="fas fa-exclamation-triangle"></i> &nbsp&nbsp<span>請將csv轉換為UTF-8格式，若有重複資料則會被忽略</span>`,
        html: `<form method="POST" id="upload_form"><input
              type="file"
              name="file"
              class="upload"
              id="file_upload"
              accept=".csv"
              @change="onFileChange"
            /></form>`,
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText: "確定",
        cancelButtonText: "取消",
      }).then((result) => {
        if (result.isConfirmed) {
          // console.log("ffff");
          axios({
            method: "post",
            url: "https://vmsl.iem.cyut.edu.tw/remote/php/import_user.php",
            data: new FormData(document.getElementById("upload_form")),
            headers: {
              "Content-Type":
                "application/x-www-form-urlencoded; charset=UTF-8",
            },
          }).then((result) => {
            // console.log(result.data);
            if (result.data >= 1) {
              this.$swal({
                icon: "success",
                title: "新增" + result.data + "筆資料成功 !",
                width: 350,
                toast: true,
                timer: 7000,
                showConfirmButton: false,
                position: "top",
                timerProgressBar: true,
              });
              this.get_user();
            } else {
              this.$swal({
                icon: "error",
                title: "新增失敗 !",
                width: 280,
                toast: true,
                showConfirmButton: false,
                timer: 5000,
                position: "top",
                timerProgressBar: true,
              });
            }
          });
        }
      });
    },
    async export_user() {
      this.$swal({
        title: "匯出使用者",
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText: "確定",
        cancelButtonText: "取消",
        footer: `<i class="fas fa-exclamation-triangle"></i> &nbsp&nbsp<span>匯出後，建議以記事本開啟此檔案執行編輯，<br>完成後以另存為編碼UTF-8格式再匯入使用者。<br>若需使用Excel開啟，請另存成csv UTF-8(逗號分隔)檔。</span><br>`,
      }).then((result) => {
        if (result.isConfirmed) {
          document.location.href =
            "https://vmsl.iem.cyut.edu.tw/remote/php/export_user.php";
        }
      });
    },
    async add_user() {
      // console.log("add");

      this.$swal({
        title: "新增使用者",
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText: "確定",
        cancelButtonText: "取消",
        html: `
          <input id="add_user" class="swal2-input" placeholder="請輸入學號" type="text" style="display: flex;transform: translateX(10px);width: 70%;">
          <input id="add_name" class="swal2-input" placeholder="請輸入姓名" type="text" style="display: flex;transform: translateX(10px);width: 70%;">`,
        width: 400,
        showLoaderOnConfirm: true,
        allowOutsideClick: () => !this.$swal.isLoading(),
        preConfirm: (input) => {
          let add_user = document.getElementById("add_user").value;
          let add_name = document.getElementById("add_name").value;

          // console.log(add_user);
          // console.log(add_name);

          if (add_user !== "" && add_name !== "") {
            axios({
              method: "post",
              url: "https://vmsl.iem.cyut.edu.tw/remote/php/add_user.php",
              data: qs.stringify({
                user: add_user,
                name: add_name,
              }),
              headers: {
                "Content-Type":
                  "application/x-www-form-urlencoded; charset=UTF-8",
              },
            }).then((result) => {
              // console.log(result.data);
              if (result.data == 1) {
                this.$swal({
                  icon: "success",
                  title: "新增成功 !",
                  width: 280,
                  toast: true,
                  showConfirmButton: false,
                  timer: 3000,
                  position: "top",
                  timerProgressBar: true,
                });
                this.get_user();
              } else if (result.data == 2) {
                this.$swal({
                  icon: "error",
                  title: "該使用者已經存在 !",
                  width: 280,
                  toast: true,
                  showConfirmButton: false,
                  timer: 5000,
                  position: "top",
                  timerProgressBar: true,
                });
              } else {
                this.$swal({
                  icon: "error",
                  title: "新增失敗 !",
                  width: 280,
                  toast: true,
                  showConfirmButton: false,
                  timer: 3000,
                  position: "top",
                  timerProgressBar: true,
                });
              }
            });
          }
        },
      });
    },
    async login() {
      this.$swal({
        title: "遠端登入管理系統",
        showConfirmButton: true,
        showCancelButton: false,
        confirmButtonText: "登入",
        html: `
        <input id="login_user" class="swal2-input" placeholder="帳號" type="text" style="display: flex;transform: translateX(10px);width: 70%;">
        <input id="login_pass" class="swal2-input" placeholder="密碼" type="password" style="display: flex;transform: translateX(10px);width: 70%;">
      `,
        width: 400,
        showLoaderOnConfirm: true,
        allowOutsideClick: false,
        preConfirm: (input_data) => {
          // console.log(input_data);
          let login_user = document.getElementById("login_user").value;
          let login_pass = document.getElementById("login_pass").value;
          return axios({
            method: "post",
            url: "https://vmsl.iem.cyut.edu.tw/remote/php/manager_login.php",
            data: qs.stringify({
              user: login_user,
              pass: login_pass,
            }),
            headers: {
              "Content-Type":
                "application/x-www-form-urlencoded; charset=UTF-8",
            },
          }).then((result) => {
            // console.log(result.data);
            if (result.data == "1") {
              this.logined = true;
              this.get_setting();
              this.get_user();
            } else {
              this.logined = false;
              this.$swal.showValidationMessage(`帳號或密碼錯誤 !`);
              // this.login();
            }
          });
          // .catch((error) => {
          //   this.$swal.showValidationMessage(`帳號或密碼錯誤`);
          // });
        },
      });
    },
    async check_login() {
      axios({
        method: "post",
        url: "https://vmsl.iem.cyut.edu.tw/remote/php/check_manager_login.php",
        data: {
          data: "data",
        },
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
      }).then((result) => {
        // console.log(result.data);

        if (result.data == "1") {
          this.logined = true;
          this.get_setting();
          this.get_user();
        } else {
          this.logined = false;
          this.login();
        }
      });
    },
    async logout() {
      this.$swal({
        icon: "warning",
        title: "確定要登出 ?",
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText: "確定",
        cancelButtonText: "取消",
      }).then((result) => {
        if (result.isConfirmed) {
          axios({
            method: "post",
            url: "https://vmsl.iem.cyut.edu.tw/remote/php/manager_logout.php",
            data: {
              data: "data",
            },
            headers: {
              "Content-Type":
                "application/x-www-form-urlencoded; charset=UTF-8",
            },
          }).then((result) => {
            console.log(result.data);
            this.check_login();
          });
        }
      });
    },
    async edit_time(start, end) {
      // console.log("qqqqq");
      // console.log(start, end);
      axios({
        method: "post",
        url: "https://vmsl.iem.cyut.edu.tw/remote/php/update_setting.php",
        data: qs.stringify({
          db_id: db_id,
          start: start,
          end: end,
        }),
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
      }).then((result) => {
        // console.log(result.data);

        if (result.data == 1) {
          this.$swal({
            icon: "success",
            title: "更新成功 !",
            width: 280,
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            position: "top",
            timerProgressBar: true,
          });
        } else {
          this.$swal({
            icon: "error",
            title: "更新失敗 !",
            width: 280,
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            position: "top",
            timerProgressBar: true,
          });
        }
        this.get_setting();
      });
    },
    async edit_status(user_id) {
      // console.log("qqqqq");
      // console.log(start, end);
      axios({
        method: "post",
        url: "https://vmsl.iem.cyut.edu.tw/remote/php/update_user_status.php",
        data: qs.stringify({
          user_id: user_id,
        }),
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
      }).then((result) => {
        // console.log(result.data);

        if (result.data == 1) {
          this.$swal({
            icon: "success",
            title: "更新成功 !",
            width: 280,
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            position: "top",
            timerProgressBar: true,
          });
        } else {
          this.$swal({
            icon: "error",
            title: "更新失敗 !",
            width: 280,
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            position: "top",
            timerProgressBar: true,
          });
        }
        this.get_user();
      });
    },
    async delete_user(user_id) {
      // console.log("qqqqq");
      // console.log(start, end);
      axios({
        method: "post",
        url: "https://vmsl.iem.cyut.edu.tw/remote/php/delete_user.php",
        data: qs.stringify({
          user_id: user_id,
        }),
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
      }).then((result) => {
        // console.log(result.data);

        if (result.data == 1) {
          this.$swal({
            icon: "success",
            title: "刪除成功 !",
            width: 280,
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            position: "top",
            timerProgressBar: true,
          });
        } else {
          this.$swal({
            icon: "error",
            title: "刪除失敗 !",
            width: 280,
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            position: "top",
            timerProgressBar: true,
          });
        }
        this.get_user();
      });
    },

    async reset() {
      this.$swal({
        icon: "warning",
        input: "checkbox",
        title: "初始化設定",
        html: '此功能會將"所有使用者"的"租借狀態"變為"未租借"，<br/>並且更新所有電腦的開機狀態至最初狀態。',
        inputValue: 0,
        showConfirmButton: true,
        showCancelButton: true,
        inputPlaceholder: "我已了解此功能",
        confirmButtonText: "確定",
        cancelButtonText: "取消",
        inputValidator: (result) => {
          return !result && "必須勾選同意方塊";
        },
      }).then((result) => {
        if (result.isConfirmed) {
          this.$swal({
            icon: "success",
            title: "成功 !",
            width: 280,
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            position: "top",
            timerProgressBar: true,
          });
          this.get_user();
        }
      });

      axios({
        method: "post",
        url: "https://e620.iem.cyut.edu.tw/bat/reset.php",
        data: "",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
      }).then((result) => {
        if (result.data > 0) {
          this.$swal({
            icon: "success",
            title: "更新成功!",
            showConfirmButton: true,
            timer: 5000,
            timerProgressBar: true,
          });
          document.getElementById("file_upload").value = "";
        }
      });
    },
    async login_record() {
      this.$swal({
        title: "登入紀錄",
        text: "將跳轉至紀錄頁面！",
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText: "確定",
        cancelButtonText: "取消",
      }).then((result) => {
        if (result.isConfirmed) {
          document.location.href =
            "https://vmsl.iem.cyut.edu.tw/remote/login_record.txt";
        }
      });
    },
    async borrow_record() {
      this.$swal({
        title: "租借紀錄",
        text: "將跳轉至紀錄頁面！",
        showConfirmButton: true,
        showCancelButton: true,
        confirmButtonText: "確定",
        cancelButtonText: "取消",
      }).then((result) => {
        if (result.isConfirmed) {
          document.location.href =
            "https://vmsl.iem.cyut.edu.tw/remote/borrow&return_record.txt";
        }
      });
    },
  },
  created() {
    this.check_login();
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style lang="scss">
// @import "~bootstrap/scss/bootstrap";

input[type="time"] {
  border: none;
  background: transparent;
}
input[type="file"] {
  transform: translateX(50px);
}

input[type="time"]:hover {
  cursor: pointer;
}

.btnn,
.btnn2,
.btnn3,
.btnn4,
.btnn5,
.btnn6 {
  display: block;
  position: absolute;
  width: 130px;
  height: 50px;
  margin: 10px auto 10px;
  border: none;
  border-radius: 10px;
  background: #7289da;
  color: #fff;
  text-align: center;
  background-size: 200%;
  outline: none;
  transition: 0.5s;
  font-size: 16px;
  right: 20px;
  top: 10px;
}
.btnn2 {
  top: 80px;
}
.btnn3 {
  top: 150px;
}

.btnn4 {
  top: 220px;
}
.btnn5 {
  top: 290px;
}

.btnn6 {
  top: 360px;
}

.table {
  position: relative;
  transform: translateY(15px);
  table-layout: fixed;
  text-align: center;
}
.btnn:hover,
.btnn2:hover,
.btnn3:hover,
.btnn4:hover,
.btnn5:hover,
.btnn6:hover {
  background: #6376ba;
}

.logout {
  position: absolute;
  top: 10px;
  left: 20px;
  font-size: 20px;
  width: 20px;
}
#form1,
#form2 {
  width: 70%;
  background: rgba(204, 229, 243, 0.95);
  height: 642px;
  padding: 5px 30px;
  border-radius: 15px;
  // left: 50%;
  // top: 50%;
  // transform: translate(-50%, -50%);
  // box-shadow: 0 0 30px 30px rgba(230, 230, 230, 0.6),
  //   inset 0 0 50px 30px rgba(230, 230, 230, 0.6);
  overflow-y: auto;
  font-size: 16px;
  margin: 40px;
}

#form1 h2,
#form2 h2 {
  text-align: center;
  margin: 0 0 20px;
}
.edit_time,
.edit_status,
.delete_user {
  cursor: pointer;
  color: black;
}

.gray {
  color: #999;
}
.green {
  color: rgb(70, 190, 54);
}

#swal2-checkbox {
  -webkit-appearance: checkbox;
}
.content {
  height: 100vh;
  justify-content: center;
  display: flex;
  align-items: center;
}

@media screen and (min-width: 450px) {
  #form1,
  #form2 {
    width: 500px;
  }
}
</style>