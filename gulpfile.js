const gulp = require("gulp");
const sass = require("gulp-sass");
//src 配下のプログラムに変更があったら更新する
const browserSync = require("browser-sync").create();
const plumber = require("gulp-plumber"); // < gulp-plumberを使います
// sass
gulp.task("sass", function(done) {
  gulp
    .src("scss/**/*.scss")
    .pipe(plumber())
    .pipe(sass({ outputStyle: "expanded" }))
    .pipe(gulp.dest("./css/"));
  done();
});

//reload
gulp.task("bs-reload", function(done) {
  browserSync.reload();
  done();
});

// watch
gulp.task("watch", function(done) {
  gulp.watch("scss/**/*.scss", gulp.series("sass","bs-reload"));
  gulp.watch("scss/**/*.scss", gulp.task("sass"));
  gulp.watch("scss/*.scss", gulp.task("sass"));
  done();
});

gulp.task("default", gulp.series(gulp.parallel("sass", "watch")));
