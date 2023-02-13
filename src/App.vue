<template>
  <router-view v-on:manage_mode="manage($event)"></router-view>
  <div v-if="manager"></div>

  <div v-else="manager">
    <home v-if="logged_in" />
    <login v-else="logged_in" v-on:update_logged_in="update($event)" />
  </div>
</template>

<script>
import login from "./components/login.vue";
import home from "./components/home.vue";

import axios from "axios";

export default {
  name: "App",
  components: {
    login,
    home,
  },
  data() {
    return {
      logged_in: false,
      manager: false,
    };
  },
  created() {
    this.check_login();
  },
  methods: {
    update(status) {
      // login.vue 傳來的登入狀態(子傳父) https://www.youtube.com/watch?v=hA9wY9tlQhg
      this.logged_in = status;
    },
    manage(status) {
      // 如果網址打/manage，就會回傳true回來，就能修改頁面內容
      // login.vue 傳來的登入狀態(子傳父) https://www.youtube.com/watch?v=hA9wY9tlQhg
      this.manager = status;
    },
    check_login() {
      axios({
        method: "post",
        url: "https://vmsl.iem.cyut.edu.tw/remote/php/check_login.php",
        data: {
          data: "data",
        },
        headers: {
          "Content-Type": "application/x-www-form-urlencoded; charset=UTF-8",
        },
      }).then((result) => {
        console.log(result.data);
        this.logged_in = result.data == "1" ? true : false;
      });
    },
  },
};
</script>

<style>
body {
  width: 100vw;
  height: 100vh;
  min-height: calc(var(--vh, 1vh) * 100);
  background: url(assets/background2.jpg);
  background-size: cover;
}
#app {
  /* display: flex; */
}
</style>
