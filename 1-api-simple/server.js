"use strict";

const buildApp = require("./src/app");

buildApp()
  .then((app) => app.listen({ port: 3000, host: "0.0.0.0" }))
  .catch((err) => {
    console.error(err);
    process.exit(1);
  });
