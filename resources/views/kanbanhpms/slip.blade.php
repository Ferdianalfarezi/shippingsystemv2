<!DOCTYPE html>
<html>
<head>
  <link href="https://fonts.googleapis.com/css2?family=Inter&display=swap" rel="stylesheet">
</head>

<style>
    @font-face {
    font-family: "Courier-Regular";
    src: url('/fonts/heisei.otf') format('opentype');
    font-style: normal;
}

/* Global override - kena semua text di frame-2 */
.frame-2 div {
    letter-spacing: 0.05em;
    font-weight: 200;
    font-size: 11px;
}
.frame-2,
.frame-2 * {
  box-sizing: border-box;
}
.frame-2 {
  background: #ffffff;
  height: 449px;
  position: relative;
  overflow: hidden;
}
.image-1 {
  width: 1330px;
  height: 449px;
  position: absolute;
  left: 0px;
  top: 0px;
  object-fit: cover;
  aspect-ratio: 1330/449;
}
.line-1 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 449px;
  height: 0px;
  position: absolute;
  left: 502px;
  top: -1px;
  transform-origin: 0 0;
  transform: rotate(90.255deg) scale(1, 1);
}
.line-2 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 449px;
  height: 0px;
  position: absolute;
  left: 957px;
  top: 0px;
  transform-origin: 0 0;
  transform: rotate(90.255deg) scale(1, 1);
}
.line-3 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 181px;
  height: 0px;
  position: absolute;
  left: 977px;
  top: 48px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-55 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 271px;
  height: 0px;
  position: absolute;
  left: 935px;
  top: 17px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-56 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 242px;
  height: 0px;
  position: absolute;
  left: 524px;
  top: 47px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-97 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 332px;
  height: 0px;
  position: absolute;
  left: 480px;
  top: 18phppx;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-98 {
  margin-top: 0px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 302px;
  height: 0px;
  position: absolute;
  left: 13px;
  top: 47px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-99 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 61px;
  height: 0px;
  position: absolute;
  left: 120px;
  top: 18px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-100 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 165px;
  top: 18px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-101 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 310px;
  top: 18px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-102 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 354px;
  top: 18px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-103 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 397px;
  top: 18px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-104 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 428px;
  top: 18px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-105 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 121px;
  height: 0px;
  position: absolute;
  left: 390px;
  top: 50px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-106 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 60px;
  height: 0px;
  position: absolute;
  left: 356px;
  top: 169px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-107 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 121px;
  height: 0px;
  position: absolute;
  left: 292px;
  top: 50px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-108 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 176px;
  top: 79px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-109 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 106px;
  top: 199px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-124 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 30px;
  height: 0px;
  position: absolute;
  left: 60px;
  top: 260px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-127 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 119px;
  height: 0px;
  position: absolute;
  left: 160px;
  top: 231px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-170 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 90px;
  height: 0px;
  position: absolute;
  left: 216px;
  top: 230px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-173 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 70px;
  height: 0px;
  position: absolute;
  left: 76px;
  top: 279px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-171 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 248px;
  top: 259px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-172 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 349px;
  top: 259px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-125 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 30px;
  height: 0px;
  position: absolute;
  left: 93px;
  top: 260px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-126 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 30px;
  height: 0px;
  position: absolute;
  left: 127px;
  top: 260px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-110 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 123px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-111 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 140px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-112 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 156px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-128 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 143px;
  top: 279px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-129 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 110px;
  top: 279px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-130 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 93px;
  top: 309px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-131 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 93px;
  top: 338px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-132 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 110px;
  top: 338px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-133 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 126px;
  top: 338px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-134 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 143px;
  top: 338px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-135 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 143px;
  top: 309px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-138 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 233px;
  top: 309px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-139 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 250px;
  top: 309px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-140 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 266px;
  top: 309px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-141 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 283px;
  top: 309px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-142 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 300px;
  top: 309px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-143 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 316px;
  top: 309px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-144 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 333px;
  top: 309px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-145 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 346px;
  top: 309px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-146 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 363px;
  top: 309px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-152 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 397px;
  top: 279px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-153 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 413px;
  top: 279px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-154 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 430px;
  top: 279px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-155 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 447px;
  top: 279px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-156 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 463px;
  top: 279px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-147 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 332px;
  top: 279px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-148 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 315px;
  top: 279px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-149 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 299px;
  top: 279px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-150 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 281px;
  top: 279px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-151 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 265px;
  top: 279px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-136 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 126px;
  top: 309px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-137 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 110px;
  top: 309px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-113 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 173px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-114 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 190px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-115 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 207px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-116 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 223px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-117 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 240px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-118 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 256px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-119 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 273px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-120 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 290px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-121 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 306px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-122 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 323px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-123 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 340px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-57 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 632px;
  top: 48px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-58 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 631px;
  top: 108px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-59 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 619px;
  top: 197px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-94 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 685px;
  top: 17px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-60 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 619px;
  top: 257px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-61 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 585px;
  top: 258px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-62 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 652px;
  top: 257px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-65 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 818px;
  top: 258px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-66 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 852px;
  top: 198px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-92 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 60px;
  height: 0px;
  position: absolute;
  left: 772px;
  top: 137px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-93 {
  width: 90.5px;
  height: 0px;
  position: absolute;
  left: 736px;
  top: 17px;
  transform: translate(-1px, 0px);
  overflow: visible;
}
.line-67 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 835px;
  top: 218px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-68 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 819px;
  top: 218px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-69 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 802px;
  top: 218px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-70 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 785px;
  top: 218px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-71 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 768px;
  top: 218px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-72 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 735px;
  top: 218px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-73 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 718px;
  top: 218px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-77 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 669px;
  top: 218px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-78 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 652px;
  top: 218px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-79 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 635px;
  top: 218px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-80 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 602px;
  top: 277px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-81 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 635px;
  top: 277px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-82 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 669px;
  top: 277px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-83 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 702px;
  top: 277px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-84 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 718px;
  top: 277px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-85 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 735px;
  top: 277px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-86 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 835px;
  top: 277px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-87 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 852px;
  top: 277px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-88 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 869px;
  top: 277px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-89 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 885px;
  top: 277px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-90 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 902px;
  top: 277px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-91 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 919px;
  top: 277px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-74 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 702px;
  top: 218px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-75 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 702px;
  top: 218px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-76 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 11px;
  height: 0px;
  position: absolute;
  left: 702px;
  top: 218px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-63 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 71px;
  height: 0px;
  position: absolute;
  left: 685px;
  top: 218px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-158 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 90px;
  height: 0px;
  position: absolute;
  left: 382px;
  top: 260px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-64 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 71px;
  height: 0px;
  position: absolute;
  left: 752px;
  top: 218px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-4 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 212px;
  height: 0px;
  position: absolute;
  left: 1309px;
  top: 17px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-5 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 91px;
  height: 0px;
  position: absolute;
  left: 1184px;
  top: 48px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-6 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 1084px;
  top: 48px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-7 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 1127px;
  top: 17px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-8 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 1077px;
  top: 17px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-9 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 1077px;
  top: 138px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-10 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 1020px;
  top: 198px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-15 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1037px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-16 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1070px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-25 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1094px;
  top: 159px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-26 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1110px;
  top: 159px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-27 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1127px;
  top: 159px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-28 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1144px;
  top: 159px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-29 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1160px;
  top: 159px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-30 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1177px;
  top: 159px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-31 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1194px;
  top: 159px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-32 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1210px;
  top: 159px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-33 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1227px;
  top: 159px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-34 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1244px;
  top: 159px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-35 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1261px;
  top: 159px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-36 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1277px;
  top: 159px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-37 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1294px;
  top: 159px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-17 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1104px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-18 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1209px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-19 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1225px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-20 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1242px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-22 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1259px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-23 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1275px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-24 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1292px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-21 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 10px;
  height: 0px;
  position: absolute;
  left: 1225px;
  top: 219px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-11 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 1053px;
  top: 198px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-12 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 31px;
  height: 0px;
  position: absolute;
  left: 1087px;
  top: 198px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-13 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 62px;
  height: 0px;
  position: absolute;
  left: 1120px;
  top: 167px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-14 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 62px;
  height: 0px;
  position: absolute;
  left: 1194px;
  top: 167px;
  transform-origin: 0 0;
  transform: rotate(90deg) scale(1, 1);
}
.line-38 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 233px;
  height: 0px;
  position: absolute;
  left: 1077px;
  top: 18px;
  transform-origin: 0 0;
  transform: rotate(0.246deg) scale(1, 1);
}
.line-39 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 333px;
  height: 0px;
  position: absolute;
  left: 977px;
  top: 49px;
  transform-origin: 0 0;
  transform: rotate(0deg) scale(1, 1);
}
.line-40 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 331px;
  height: 0px;
  position: absolute;
  left: 978px;
  top: 79px;
  transform-origin: 0 0;
  transform: rotate(0deg) scale(1, 1);
}
.line-41 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 331px;
  height: 0px;
  position: absolute;
  left: 978px;
  top: 109px;
  transform-origin: 0 0;
  transform: rotate(0deg) scale(1, 1);
}
.line-42 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 331px;
  height: 0px;
  position: absolute;
  left: 978px;
  top: 139px;
  transform-origin: 0 0;
  transform: rotate(0deg) scale(1, 1);
}
.line-43 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 331px;
  height: 0px;
  position: absolute;
  left: 978px;
  top: 169px;
  transform-origin: 0 0;
  transform: rotate(0deg) scale(1, 1);
}
.line-44 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 331px;
  height: 0px;
  position: absolute;
  left: 978px;
  top: 199px;
  transform-origin: 0 0;
  transform: rotate(0deg) scale(1, 1);
}
.line-45 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 331px;
  height: 0px;
  position: absolute;
  left: 979px;
  top: 229px;
  transform-origin: 0 0;
  transform: rotate(0deg) scale(1, 1);
}
.line-46 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 413px;
  height: 0px;
  position: absolute;
  left: 524px;
  top: 289px;
}
.line-157 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 468px;
  height: 0px;
  position: absolute;
  left: 12px;
  top: 350px;
}
.line-160 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 468px;
  height: 0px;
  position: absolute;
  left: 13px;
  top: 289px;
}
.line-161 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 468px;
  height: 0px;
  position: absolute;
  left: 13px;
  top: 259px;
}
.line-162 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 468px;
  height: 0px;
  position: absolute;
  left: 12px;
  top: 229px;
}
.line-163 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 468px;
  height: 0px;
  position: absolute;
  left: 12px;
  top: 199px;
}
.line-164 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 468px;
  height: 0px;
  position: absolute;
  left: 12px;
  top: 169px;
}
.line-165 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 468px;
  height: 0px;
  position: absolute;
  left: 13px;
  top: 139px;
}
.line-166 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 468px;
  height: 0px;
  position: absolute;
  left: 13px;
  top: 109px;
}
.line-167 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 468px;
  height: 0px;
  position: absolute;
  left: 12px;
  top: 79px;
}
.line-168 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 468px;
  height: 0px;
  position: absolute;
  left: 12px;
  top: 49px;
}
.line-169 {
  margin-top: 0px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 362px;
  height: 0px;
  position: absolute;
  left: 118px;
  top: 15px;
  transform-origin: 0 0;
  transform: rotate(0deg) scale(1, 1);
}
.line-159 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 368px;
  height: 0px;
  position: absolute;
  left: 14px;
  top: 319px;
}
.line-47 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 413px;
  height: 0px;
  position: absolute;
  left: 524px;
  top: 259px;
}
.line-48 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 413px;
  height: 0px;
  position: absolute;
  left: 524px;
  top: 229px;
}
.line-95 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 252px;
  height: 0px;
  position: absolute;
  left: 685px;
  top: 19px;
}
.line-96 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 201px;
  height: 0px;
  position: absolute;
  left: 736px;
  top: 93px;
}
.line-49 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 413px;
  height: 0px;
  position: absolute;
  left: 524px;
  top: 199px;
}
.line-50 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 413px;
  height: 0px;
  position: absolute;
  left: 524px;
  top: 169px;
}
.line-51 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 413px;
  height: 0px;
  position: absolute;
  left: 524px;
  top: 139px;
}
.line-52 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 413px;
  height: 0px;
  position: absolute;
  left: 524px;
  top: 109px;
}
.line-53 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 213px;
  height: 0px;
  position: absolute;
  left: 524px;
  top: 79px;
}
.line-54 {
  margin-top: -2px;
  border-style: solid;
  border-color: #000000;
  border-width: 2px 0 0 0;
  width: 413px;
  height: 0px;
  position: absolute;
  left: 524px;
  top: 49px;
}
.hpm-supplier {
  color: #000000;
  text-align: right;
  font-family: "Inter-Regular", sans-serif;
  font-size: 18px;
 
  font-weight: 300;
    letter-spacing: 0.06em;
  position: absolute;
  left: 1212px;
  top: 393px;
  width: 99px;
  height: 33px;
}
.hpm-to-be-returned-tosupplier {
  color: #000000;
  text-align: right;
  font-family: "Inter-Regular", sans-serif;
  font-size: 18px;
  font-weight: 400;
  position: absolute;
  left: 651px;
  top: 394px;
  width: 284px;
  height: 34px;
}
.hpm-honda-prospect-motor {
  color: #000000;
  text-align: right;
  font-family: "Inter-Regular", sans-serif;
  font-size: 18px !important;
  font-weight: 300;
  letter-spacing: 0.06em;
  position: absolute;
  left: 192px;
  top: 393px;
  width: 284px;
  height: 34px;
}
.slip {
  color: #000000;
  text-align: right;
  font-family: "Inter-Regular", sans-serif;
  font-size: 24px !important;
  font-weight: 300;
  letter-spacing: 0.06em;
  position: absolute;
  left: 37px;
  top: 4px;
  width: 52px;
  height: 34px;
}
.receipt {
  color: #000000;
  text-align: right;
  font-family: "Inter-Regular", sans-serif;
  font-size: 24px;
  font-weight: 300;
  letter-spacing: 0.06em;
  position: absolute;
  left: 523px;
  top: 3px;
  width: 116px;
  height: 32px;
}
.copy {
  color: #000000;
  text-align: right;
  font-family: "Inter-Regular", sans-serif;
  font-size: 24px;
  ffont-weight: 300;
  letter-spacing: 0.06em;
  position: absolute;
  left: 935px;
  top: 3px;
  width: 116px;
  height: 32px;
}
.order {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 14px;
  font-weight: 300;
  letter-spacing: 0.06em;
  position: absolute;
  left: 17px;
  top: 27px;
  width: 76px;
  height: 30px;
}
.location-cd {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 300;
  letter-spacing: 0.06em;
  position: absolute;
  left: 3px;
  top: 47px;
  width: 93px;
  height: 30px;
}
.location-cd2 {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 300;
  letter-spacing: 0.06em;
  position: absolute;
  left: 515px;
  top: 47px;
  width: 93px;
  height: 30px;
}
.location-cd3 {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 300;
  letter-spacing: 0.06em;
  position: absolute;
  left: 967px;
  top: 48px;
  width: 93px;
  height: 30px;
}
.slip-no {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 300;
  letter-spacing: 0.06em;
  position: absolute;
  left: 126px;
  top: 18px;
  width: 93px;
  height: 30px;
}
.slip-no2 {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 698px;
  top: 17px;
  width: 93px;
  height: 30px;
}
.slip-no3 {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 300;
  letter-spacing: 0.06em;
  position: absolute;
  left: 1090px;
  top: 17px;
  width: 93px;
  height: 30px;
}
.name {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 300;
  letter-spacing: 0.06em;
  position: absolute;
  left: 65px;
  top: 48px;
  width: 93px;
  height: 30px;
}
.parts-color {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 166px;
  top: 77px;
  width: 93px;
  height: 30px;
}
.parts-color2 {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 515px;
  top: 107px;
  width: 93px;
  height: 30px;
}
.parts-color3 {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 1173px;
  top: 77px;
  width: 93px;
  height: 30px;
}
.ship-to-cd {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 275px;
  top: 47px;
  width: 93px;
  height: 30px;
}
.ship-to-cd2 {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 615px;
  top: 47px;
  width: 93px;
  height: 30px;
}
.plan-code {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 267px;
  top: 78px;
  width: 93px;
  height: 30px;
}
.plan-code2 {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 712px;
  top: 91px;
  width: 93px;
  height: 30px;
}
.plan-code3 {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 1159px;
  top: 47px;
  width: 93px;
  height: 30px;
}
.dc-no {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 239px;
  top: 107px;
  width: 93px;
  height: 30px;
}
.parts-weight {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 289px;
  top: 138px;
  width: 93px;
  height: 30px;
}
.parts-weight2 {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 770px;
  top: 138px;
  width: 93px;
  height: 30px;
}
.container {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 365px;
  top: 137px;
  width: 93px;
  height: 30px;
}
.packing {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 359px;
  top: 228px;
  width: 93px;
  height: 30px;
}
.approved-by {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 376px;
  top: 288px;
  width: 93px;
  height: 30px;
}
.qc {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 352px;
  top: 329px;
  width: 93px;
  height: 30px;
}
.qc-qty {
  color: #000000;
  text-align: left;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  line-height: 10px;
  font-weight: 400;
  position: absolute;
  left: 354px;
  top: 261px;
  width: 93px;
  height: 30px;
}
.rec-qty {
  color: #000000;
  text-align: left;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  line-height: 10px;
  font-weight: 400;
  position: absolute;
  left: 221px;
  top: 261px;
  width: 93px;
  height: 30px;
}
.rec-qty2 {
  color: #000000;
  text-align: left;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  line-height: 10px;
  font-weight: 400;
  position: absolute;
  left: 761px;
  top: 262px;
  width: 93px;
  height: 30px;
}
.rec-qty3 {
  color: #000000;
  text-align: left;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  line-height: 10px;
  font-weight: 400;
  position: absolute;
  left: 1130px;
  top: 201px;
  width: 93px;
  height: 30px;
}
.qty {
  color: #000000;
  text-align: left;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  line-height: 10px;
  font-weight: 400;
  position: absolute;
  left: 223px;
  top: 231px;
  width: 93px;
  height: 30px;
}
.qty2 {
  color: #000000;
  text-align: left;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  line-height: 10px;
  font-weight: 400;
  position: absolute;
  left: 759px;
  top: 230px;
  width: 93px;
  height: 30px;
}
.qty3 {
  color: #000000;
  text-align: left;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  line-height: 10px;
  font-weight: 400;
  position: absolute;
  left: 1201px;
  top: 170px;
  width: 93px;
  height: 30px;
}
.duty {
  color: #000000;
  text-align: left;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  line-height: 10px;
  font-weight: 400;
  position: absolute;
  left: 165px;
  top: 290px;
  width: 93px;
  height: 30px;
}
.remarks {
  color: #000000;
  text-align: left;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  line-height: 10px;
  font-weight: 400;
  position: absolute;
  left: 165px;
  top: 319px;
  width: 93px;
  height: 30px;
}
.time {
  color: #000000;
  text-align: left;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  line-height: 10px;
  font-weight: 400;
  position: absolute;
  left: 165px;
  top: 231px;
  width: 93px;
  height: 30px;
}
.time2 {
  color: #000000;
  text-align: left;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  line-height: 10px;
  font-weight: 400;
  position: absolute;
  left: 690px;
  top: 230px;
  width: 93px;
  height: 30px;
}
.time3 {
  color: #000000;
  text-align: left;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  line-height: 10px;
  font-weight: 400;
  position: absolute;
  left: 1129px;
  top: 170px;
  width: 93px;
  height: 30px;
}
.rec-date {
  color: #000000;
  text-align: left;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  line-height: 10px;
  font-weight: 400;
  position: absolute;
  left: 17px;
  top: 261px;
  width: 93px;
  height: 30px;
}
.rec-date2 {
  color: #000000;
  text-align: left;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  line-height: 10px;
  font-weight: 400;
  position: absolute;
  left: 529px;
  top: 261px;
  width: 93px;
  height: 30px;
}
.rec-date3 {
  color: #000000;
  text-align: left;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  line-height: 10px;
  font-weight: 400;
  position: absolute;
  left: 981px;
  top: 201px;
  width: 93px;
  height: 30px;
}
.excise {
  color: #000000;
  text-align: left;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  line-height: 10px;
  font-weight: 400;
  position: absolute;
  left: 17px;
  top: 291px;
  width: 93px;
  height: 30px;
}
.sale {
  color: #000000;
  text-align: left;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  line-height: 10px;
  font-weight: 400;
  position: absolute;
  left: 18px;
  top: 321px;
  width: 93px;
  height: 30px;
}
.inv-category {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 353px;
  top: 166px;
  width: 93px;
  height: 30px;
}
.inv-category2 {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 772px;
  top: 168px;
  width: 93px;
  height: 30px;
}
.sp-ord-no {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 332px;
  top: 196px;
  width: 93px;
  height: 30px;
}
.rcv-type {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 359px;
  top: 107px;
  width: 93px;
  height: 30px;
}
.supply-adr {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 373px;
  top: 78px;
  width: 93px;
  height: 30px;
}
.from-sup-adr {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 386px;
  top: 47px;
  width: 93px;
  height: 30px;
}
.spno {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 375px;
  top: 18px;
  width: 93px;
  height: 30px;
}
.hns {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 332px;
  top: 17px;
  width: 93px;
  height: 30px;
}
.ms-sp {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 302px;
  top: 17px;
  width: 93px;
  height: 30px;
}
.loc-c {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 257px;
  top: 18px;
  width: 93px;
  height: 30px;
}
.parts-no {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: -18px;
  top: 77px;
  width: 93px;
  height: 30px;
}
.parts-no2 {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 493px;
  top: 77px;
  width: 93px;
  height: 30px;
}
.parts-no3 {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 945px;
  top: 77px;
  width: 93px;
  height: 30px;
}
.parts-name {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: -3px;
  top: 107px;
  width: 93px;
  height: 30px;
}
.parts-name2 {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 614px;
  top: 107px;
  width: 93px;
  height: 30px;
}
.parts-name3 {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 959px;
  top: 107px;
  width: 93px;
  height: 30px;
}
.kd-lot-no {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: -13px;
  top: 137px;
  width: 93px;
  height: 30px;
}
.kd-lot-no2 {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 501px;
  top: 137px;
  width: 93px;
  height: 30px;
}
.production-seq-no {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: -2px;
  top: 167px;
  width: 141px;
  height: 26px;
}
.production-seq-no2 {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 509px;
  top: 167px;
  width: 141px;
  height: 26px;
}
.invoice-no {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: -54px;
  top: 198px;
  width: 141px;
  height: 26px;
}
.invoice-no2 {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 460px;
  top: 198px;
  width: 142px;
  height: 26px;
}
.invoice-no3 {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 915px;
  top: 137px;
  width: 141px;
  height: 26px;
}
.date {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: -94px;
  top: 228px;
  width: 141px;
  height: 26px;
}
.date2 {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 417px;
  top: 228px;
  width: 141px;
  height: 26px;
}
.date3 {
  color: #000000;
  text-align: right;
  font-family: "Courier-Regular", sans-serif;
  font-size: 12px;
  font-weight: 400;
  position: absolute;
  left: 869px;
  top: 167px;
  width: 141px;
  height: 26px;
}
</style>

<div class="frame-2">
  <div class="line-1"></div>
  <div class="line-2"></div>
  <div class="line-3"></div>
  <div class="line-55"></div>
  <div class="line-56"></div>
  <div class="line-97"></div>
  <div class="line-98"></div>
  <div class="line-99"></div>
  <div class="line-100"></div>
  <div class="line-101"></div>
  <div class="line-102"></div>
  <div class="line-103"></div>
  <div class="line-104"></div>
  <div class="line-105"></div>
  <div class="line-106"></div>
  <div class="line-107"></div>
  <div class="line-108"></div>
  <div class="line-109"></div>
  <div class="line-124"></div>
  <div class="line-127"></div>
  <div class="line-170"></div>
  <div class="line-173"></div>
  <div class="line-171"></div>
  <div class="line-172"></div>
  <div class="line-125"></div>
  <div class="line-126"></div>
  <div class="line-110"></div>
  <div class="line-111"></div>
  <div class="line-112"></div>
  <div class="line-128"></div>
  <div class="line-129"></div>
  <div class="line-130"></div>
  <div class="line-131"></div>
  <div class="line-132"></div>
  <div class="line-133"></div>
  <div class="line-134"></div>
  <div class="line-135"></div>
  <div class="line-138"></div>
  <div class="line-139"></div>
  <div class="line-140"></div>
  <div class="line-141"></div>
  <div class="line-142"></div>
  <div class="line-143"></div>
  <div class="line-144"></div>
  <div class="line-145"></div>
  <div class="line-146"></div>
  <div class="line-152"></div>
  <div class="line-153"></div>
  <div class="line-154"></div>
  <div class="line-155"></div>
  <div class="line-156"></div>
  <div class="line-147"></div>
  <div class="line-148"></div>
  <div class="line-149"></div>
  <div class="line-150"></div>
  <div class="line-151"></div>
  <div class="line-136"></div>
  <div class="line-137"></div>
  <div class="line-113"></div>
  <div class="line-114"></div>
  <div class="line-115"></div>
  <div class="line-116"></div>
  <div class="line-117"></div>
  <div class="line-118"></div>
  <div class="line-119"></div>
  <div class="line-120"></div>
  <div class="line-121"></div>
  <div class="line-122"></div>
  <div class="line-123"></div>
  <div class="line-57"></div>
  <div class="line-58"></div>
  <div class="line-59"></div>
  <div class="line-94"></div>
  <div class="line-60"></div>
  <div class="line-61"></div>
  <div class="line-62"></div>
  <div class="line-65"></div>
  <div class="line-66"></div>
  <div class="line-92"></div>
  <img class="line-93" src="line-930.svg" />
  <div class="line-67"></div>
  <div class="line-68"></div>
  <div class="line-69"></div>
  <div class="line-70"></div>
  <div class="line-71"></div>
  <div class="line-72"></div>
  <div class="line-73"></div>
  <div class="line-77"></div>
  <div class="line-78"></div>
  <div class="line-79"></div>
  <div class="line-80"></div>
  <div class="line-81"></div>
  <div class="line-82"></div>
  <div class="line-83"></div>
  <div class="line-84"></div>
  <div class="line-85"></div>
  <div class="line-86"></div>
  <div class="line-87"></div>
  <div class="line-88"></div>
  <div class="line-89"></div>
  <div class="line-90"></div>
  <div class="line-91"></div>
  <div class="line-74"></div>
  <div class="line-75"></div>
  <div class="line-76"></div>
  <div class="line-63"></div>
  <div class="line-158"></div>
  <div class="line-64"></div>
  <div class="line-4"></div>
  <div class="line-5"></div>
  <div class="line-6"></div>
  <div class="line-7"></div>
  <div class="line-8"></div>
  <div class="line-9"></div>
  <div class="line-10"></div>
  <div class="line-15"></div>
  <div class="line-16"></div>
  <div class="line-25"></div>
  <div class="line-26"></div>
  <div class="line-27"></div>
  <div class="line-28"></div>
  <div class="line-29"></div>
  <div class="line-30"></div>
  <div class="line-31"></div>
  <div class="line-32"></div>
  <div class="line-33"></div>
  <div class="line-34"></div>
  <div class="line-35"></div>
  <div class="line-36"></div>
  <div class="line-37"></div>
  <div class="line-17"></div>
  <div class="line-18"></div>
  <div class="line-19"></div>
  <div class="line-20"></div>
  <div class="line-22"></div>
  <div class="line-23"></div>
  <div class="line-24"></div>
  <div class="line-21"></div>
  <div class="line-11"></div>
  <div class="line-12"></div>
  <div class="line-13"></div>
  <div class="line-14"></div>
  <div class="line-38"></div>
  <div class="line-39"></div>
  <div class="line-40"></div>
  <div class="line-41"></div>
  <div class="line-42"></div>
  <div class="line-43"></div>
  <div class="line-44"></div>
  <div class="line-45"></div>
  <div class="line-46"></div>
  <div class="line-157"></div>
  <div class="line-160"></div>
  <div class="line-161"></div>
  <div class="line-162"></div>
  <div class="line-163"></div>
  <div class="line-164"></div>
  <div class="line-165"></div>
  <div class="line-166"></div>
  <div class="line-167"></div>
  <div class="line-168"></div>
  <div class="line-169"></div>
  <div class="line-159"></div>
  <div class="line-47"></div>
  <div class="line-48"></div>
  <div class="line-95"></div>
  <div class="line-96"></div>
  <div class="line-49"></div>
  <div class="line-50"></div>
  <div class="line-51"></div>
  <div class="line-52"></div>
  <div class="line-53"></div>
  <div class="line-54"></div>
  <div class="hpm-supplier">
    HPM
    <br />
    SUPPLIER
  </div>
  <div class="hpm-to-be-returned-tosupplier">
    HPM
    <br />
    TO BE RETURNED TOSUPPLIER
  </div>
  <div class="hpm-honda-prospect-motor">
    HPM
    <br />
    Honda Prospect Motor
  </div>
  <div class="slip">SLIP</div>
  <div class="receipt">RECEIPT</div>
  <div class="copy">COPY</div>
  <div class="order">(ORDER)</div>
  <div class="location-cd">LOCATION CD</div>
  <div class="location-cd">LOCATION CD</div>
  <div class="location-cd2">LOCATION CD</div>
  <div class="location-cd3">LOCATION CD</div>
  <div class="slip-no">SLIP NO</div>
  <div class="slip-no2">SLIP NO</div>
  <div class="slip-no3">SLIP NO</div>
  <div class="name">NAME</div>
  <div class="parts-color">PARTS COLOR</div>
  <div class="parts-color2">PARTS COLOR</div>
  <div class="parts-color3">PARTS COLOR</div>
  <div class="ship-to-cd">SHIP TO CD</div>
  <div class="ship-to-cd2">SHIP TO CD</div>
  <div class="plan-code">PLAN CODE</div>
  <div class="plan-code2">PLAN CODE</div>
  <div class="plan-code3">PLAN CODE</div>
  <div class="dc-no">DC NO</div>
  <div class="parts-weight">PARTS WEIGHT</div>
  <div class="parts-weight2">PARTS WEIGHT</div>
  <div class="container">CONTAINER</div>
  <div class="packing">PACKING</div>
  <div class="approved-by">APPROVED BY</div>
  <div class="qc">(QC)</div>
  <div class="qc-qty">
    QC
    <br />
    QTY
  </div>
  <div class="rec-qty">
    REC
    <br />
    QTY
  </div>
  <div class="rec-qty2">
    REC
    <br />
    QTY
  </div>
  <div class="rec-qty2">
    REC
    <br />
    QTY
  </div>
  <div class="rec-qty3">
    REC
    <br />
    QTY
  </div>
  <div class="qty">QTY</div>
  <div class="qty2">QTY</div>
  <div class="qty3">QTY</div>
  <div class="duty">DUTY</div>
  <div class="remarks">REMARKS</div>
  <div class="time">TIME</div>
  <div class="time2">TIME</div>
  <div class="time3">TIME</div>
  <div class="rec-date">
    REC
    <br />
    DATE
  </div>
  <div class="rec-date2">
    REC
    <br />
    DATE
  </div>
  <div class="rec-date3">
    REC
    <br />
    DATE
  </div>
  <div class="excise">
    EXCISE
    <br />
    (%)
  </div>
  <div class="sale">
    SALE
    <br />
    (%)
  </div>
  <div class="inv-category">INV CATEGORY</div>
  <div class="inv-category2">INV CATEGORY</div>
  <div class="sp-ord-no">SP ORD NO</div>
  <div class="rcv-type">RCV TYPE</div>
  <div class="supply-adr">SUPPLY ADR</div>
  <div class="from-sup-adr">FROM SUP ADR</div>
  <div class="spno">SPNO</div>
  <div class="hns">HNS</div>
  <div class="ms-sp">MS/SP</div>
  <div class="loc-c">LOC.C</div>
  <div class="from-sup-adr">FROM SUP ADR</div>
  <div class="parts-no">PARTS NO</div>
  <div class="parts-no2">PARTS NO</div>
  <div class="parts-no3">PARTS NO</div>
  <div class="parts-name">PARTS NAME</div>
  <div class="parts-name2">PARTS NAME</div>
  <div class="parts-name3">PARTS NAME</div>
  <div class="kd-lot-no">KD LOT NO</div>
  <div class="kd-lot-no2">KD LOT NO</div>
  <div class="production-seq-no">PRODUCTION SEQ NO</div>
  <div class="production-seq-no2">PRODUCTION SEQ NO</div>
  <div class="invoice-no">INVOICE NO</div>
  <div class="invoice-no2">INVOICE NO</div>
  <div class="invoice-no3">INVOICE NO</div>
  <div class="date">DATE</div>
  <div class="date2">DATE</div>
  <div class="date3">DATE</div>
</div>
