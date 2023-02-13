<template>
  <form class="login-form" method="POST">
    <h2>朝陽工管遠端登入</h2>
    <div class="txtb">
      <span><i class="fas fa-user"></i> 帳號</span>
      <input
        v-on:keyup.enter="login_btn"
        type="text"
        v-model="user"
        ref="input_user"
      />
    </div>
    <div class="txtb">
      <span><i class="fas fa-key"></i> 密碼</span>
      <input v-on:keyup.enter="login_btn" type="password" v-model="pass" />
    </div>
    <span class="logbtn" @click="login_btn">Login</span>
  </form>
</template>

<script>
import axios from "axios";
import qs from "qs"; // 轉換格式用

export default {
  data() {
    return {
      user: "",
      pass: "",
    };
  },
  mounted() {
    this.$refs.input_user.focus();
  },

  methods: {
    // 子傳父的教學 https://www.youtube.com/watch?v=hA9wY9tlQhg
    login_btn() {
      axios({
        method: "post",
        url: "https://vmsl.iem.cyut.edu.tw/remote/php/login.php",
        data: qs.stringify({
          user: this.user,
          pass: this.pass,
        }),
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
      }).then((result) => {
        // console.log(result.data);
        if (result.data == 0) {
          this.$swal({
            icon: "error",
            title: "登入失敗 !",
            width: 280,
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            position: "top",
            timerProgressBar: true,
          });
          this.$emit("update_logged_in", false);
        } else {
          this.$swal({
            icon: "success",
            title: "登入成功 !",
            width: 280,
            toast: true,
            showConfirmButton: false,
            timer: 3000,
            position: "top",
            timerProgressBar: true,
          });
          // document.cookie = "cyut_ime_vmsl_remote=" + user_id + "; path=/;"; //將userid放入cookie
          // if (result.data == 1) {
          //   // 未租用狀態
          //   console.log(111);
          // } else if (result.data == 2) {
          //   // 已租用狀態
          //   console.log(222);
          // }
          this.$emit("update_logged_in", true);
        }
      });
    },
  },
};
</script>

<!-- Add "scoped" attribute to limit CSS to this component only -->
<style>
.login-form h2 {
  text-align: center;
  font-weight: 900;
}
</style>