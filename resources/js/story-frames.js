function shape(context, points, fill) {
    context.beginPath();
    points.forEach(([x, y], index) => index ? context.lineTo(x, y) : context.moveTo(x, y));
    context.closePath();
    context.fillStyle = fill;
    context.fill();
}

function dots(context, x, y, color, columns = 10, rows = 6) {
    context.fillStyle = color;
    for (let row = 0; row < rows; row++) {
        for (let column = 0; column < columns; column++) {
            context.beginPath();
            context.arc(x + column * 20, y + row * 20, 3.5, 0, Math.PI * 2);
            context.fill();
        }
    }
}

function shuttle(context, x, y, scale, angle, accent) {
    context.save();
    context.translate(x, y);
    context.rotate(angle);
    context.scale(scale, scale);
    context.lineJoin = 'round';
    context.lineWidth = 5;
    context.strokeStyle = '#102656';
    for (let feather = -2; feather <= 2; feather++) {
        context.save();
        context.rotate(feather * .17);
        context.beginPath();
        context.moveTo(-13, 32);
        context.bezierCurveTo(-26, -40, -44, -127, -16, -142);
        context.bezierCurveTo(20, -166, 33, -62, 14, 32);
        context.closePath();
        context.fillStyle = feather % 2 === 0 ? '#ffffff' : '#d6e8ff';
        context.fill();
        context.stroke();
        context.beginPath();
        context.moveTo(0, -126);
        context.lineTo(0, 26);
        context.strokeStyle = '#2455f570';
        context.lineWidth = 2;
        context.stroke();
        context.restore();
    }
    context.fillStyle = '#ffffff';
    context.beginPath();
    context.roundRect(-32, 18, 64, 65, [8, 8, 30, 30]);
    context.fill();
    context.stroke();
    context.fillStyle = accent;
    context.fillRect(-32, 19, 64, 18);
    context.restore();
}

function racket(context, x, y, scale, angle, color) {
    context.save();
    context.translate(x, y);
    context.rotate(angle);
    context.scale(scale, scale);
    context.lineWidth = 13;
    context.strokeStyle = '#ffffff';
    context.beginPath();
    context.ellipse(0, 0, 65, 92, 0, 0, Math.PI * 2);
    context.stroke();
    context.lineWidth = 7;
    context.strokeStyle = color;
    context.stroke();
    context.save();
    context.beginPath();
    context.ellipse(0, 0, 58, 85, 0, 0, Math.PI * 2);
    context.clip();
    context.lineWidth = 1.5;
    context.strokeStyle = '#ffffffc0';
    for (let line = -80; line <= 80; line += 14) {
        context.beginPath();
        context.moveTo(line, -100);
        context.lineTo(line, 100);
        context.moveTo(-80, line);
        context.lineTo(80, line);
        context.stroke();
    }
    context.restore();
    context.lineWidth = 11;
    context.strokeStyle = '#ffffff';
    context.beginPath();
    context.moveTo(0, 92);
    context.lineTo(0, 188);
    context.stroke();
    context.strokeStyle = color;
    context.lineWidth = 17;
    context.beginPath();
    context.moveTo(0, 155);
    context.lineTo(0, 204);
    context.stroke();
    context.restore();
}

export function drawFrameBackground(context, theme) {
    if (theme === 'blue') {
        shape(context, [[500, 0], [1080, 0], [1080, 325], [890, 325]], '#2455f5');
        shape(context, [[756, 0], [840, 0], [1080, 240], [1080, 322]], '#ffd23f');
        shape(context, [[0, 1420], [260, 1680], [600, 1920], [0, 1920]], '#2455f5');
        dots(context, 390, 35, '#ffffff45', 9, 7);
        dots(context, 720, 1775, '#ffffff30', 15, 7);
    } else if (theme === 'yellow') {
        context.fillStyle = '#102656';
        context.beginPath();
        context.arc(1100, 60, 420, 0, Math.PI * 2);
        context.fill();
        dots(context, 670, 20, '#ffd23f70', 20, 12);
        shape(context, [[0, 1590], [340, 1690], [810, 1920], [0, 1920]], '#102656');
        shape(context, [[1080, 1560], [1080, 1920], [650, 1920]], '#2455f5');
        dots(context, 10, 22, '#10265635', 9, 10);
    } else {
        context.fillStyle = '#d8eee9';
        context.fillRect(0, 0, 1080, 1920);
        shape(context, [[630, 0], [1080, 0], [1080, 420]], '#2455f5');
        shape(context, [[0, 1450], [520, 1920], [0, 1920]], '#ffd23f');
        dots(context, 680, 1770, '#10265635', 18, 8);
        context.strokeStyle = '#10265635';
        context.lineWidth = 2;
        for (let line = 0; line < 6; line++) {
            context.strokeRect(10 + line * 15, 30 + line * 15, 330, 200);
        }
    }
}

export function drawFrameForeground(context, theme) {
    const yellow = theme === 'yellow';
    const collage = theme === 'minimal';
    context.strokeStyle = collage ? '#ffffff' : '#ffd23f';
    context.lineWidth = collage ? 14 : 6;
    context.beginPath();
    context.roundRect(48, 260, 984, 1420, 22);
    context.stroke();
    if (collage) {
        shape(context, [[18, 247], [186, 220], [198, 276], [30, 303]], '#ffd23fe8');
        shape(context, [[890, 1658], [1058, 1627], [1070, 1683], [902, 1714]], '#ffd23fe8');
    } else {
        shape(context, [[18, 350], [18, 240], [244, 240], [217, 284], [62, 284]], yellow ? '#2455f5' : '#ffd23f');
        shape(context, [[830, 1696], [1048, 1696], [1048, 1560], [1004, 1586], [1004, 1652], [856, 1652]], yellow ? '#ffd23f' : '#2455f5');
    }
    context.save();
    context.translate(730, 210);
    context.rotate(-.09);
    context.fillStyle = '#ffffff';
    context.fillRect(-12, -40, 246, 68);
    context.fillStyle = '#102656';
    context.font = '800 italic 28px "Plus Jakarta Sans", sans-serif';
    context.fillText('LET’S PLAY!', 6, 5);
    context.restore();
    shuttle(context, 963, 122, .92, .65, '#ffd23f');
    racket(context, 934, 1570, .82, .55, yellow ? '#ffd23f' : '#2455f5');
    shuttle(context, 103, 1650, .63, -.65, '#ffd23f');
    context.save();
    context.translate(58, 1740);
    context.rotate(collage ? -.025 : -.045);
    context.fillStyle = '#102656';
    context.fillRect(6, 8, 924, 86);
    context.fillStyle = '#ffd23f';
    context.fillRect(0, 0, 924, 86);
    context.fillStyle = '#102656';
    context.font = '800 italic 42px "Plus Jakarta Sans", sans-serif';
    context.fillText('MAIN BARENG, SEHAT & SERU!', 24, 56, 875);
    context.restore();
    context.fillStyle = yellow || collage ? '#102656' : '#ffffff';
    if (yellow) {
        context.fillStyle = '#ffffff';
    }
    context.font = '700 23px "Plus Jakarta Sans", sans-serif';
    context.fillText('NGEBADMINTONYUK', 58, 1868);
    context.textAlign = 'right';
    context.fillText('BADMINTON COMMUNITY', 1022, 1868);
    context.textAlign = 'left';
}
