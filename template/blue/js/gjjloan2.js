var l1_5 = 0.0405;
var l6_30 = 0.0459;
var $lilv = $('.gjj[name="lilv"]').change(function() {
    setlilv(this.value)
});;
function setlilv(v) {
    l1_5 = parseFloat(lilv_array[v][2][5]);
    l6_30 = parseFloat(lilv_array[v][2][10]);
}
$.getScript(tplurl + 'js/ll.js',function () {
    setlilv($lilv.val());
})

function gjjloan2(obj) 
{

    var dknx;
    var syhk;
    var dked;
    var hkfs;
    var bxhj;
    var bxhj2;
    var r;
    var rb;

    dknx = Math.round(obj.mount10.value);
    if (dknx <= 0) {
        alert('贷款申请年限不能为空,请输入');
        obj.mount10.value = '';
        return;
    }
    var bcv = 0;
    if (dknx > 5) 
    {
        bcv = Math.round(1000000 * l6_30 / 12) / 1000000;
    } else {
        bcv = Math.round(1000000 * l1_5 / 12) / 1000000;
    }

    dked = Math.round(obj.need.value * 10) / 10;
    obj.need.value = dked;
    if (dked == 0) {
        alert('需要的贷款额度不能为空,请输入');
        obj.need.value = '';
        return;
    }
    if (dked < 0) {
        alert('输入的贷款额度不符合要求,请输入');
        obj.need.value = '';
        return;
    }
    hkfs = obj.select.value;
    if (hkfs == 1) {
        var ylv_new;
        if (dknx >= 1 && dknx <= 5)
            ylv_new = l1_5 / 12;
        else
            ylv_new = l6_30 / 12;
        var ncm = parseFloat(ylv_new) + 1;
        var dknx_new = dknx * 12;
        var total_ncm = Math.pow(ncm, dknx_new)
        ze22.innerHTML = Math.round(((dked * 10000 * ylv_new * total_ncm) / (total_ncm - 1)) * 100) / 100;
        var pp = Math.round(((dked * 10000 * ylv_new * total_ncm) / (total_ncm - 1)) * 100) / 100;
        bxhj = Math.round(pp * dknx * 12 * 100) / 100;
        lx2.innerHTML = bxhj;
    }
    if (hkfs == 2) 
    {
        if (dknx > 5) 
        {
            rb = l6_30 * 100;
        } else {
            rb = l1_5 * 100;
        }
        syhk = Math.round((dked * 10000 / (dknx * 12) + dked * 10000 * rb / (100 * 12)) * 100) / 100;
        sfk2.innerHTML = syhk;
        var yhke;
        var bxhj;
        var dkys;
        var sydkze;
        var yhkbj;
        dkys = dknx * 12;
        yhkbj = dked * 10000 / dkys;
        yhke = syhk;
        sydkze = dked * 10000 - yhkbj;
        bxhj = syhk;
        for (var count = 2; count <= dkys; ++count) 
        {
            yhke = dked * 10000 / dkys + sydkze * rb / 1200;
            sydkze -= yhkbj;
            bxhj += yhke;
        }
        lx3.innerHTML = Math.round(bxhj * 100) / 100;
    }
    if (hkfs == 3) 
    {
        switch (dknx) {
            case 1:
                rb = 83.04 / 100;
                break;
            case 2:
                rb = 81.08 / 100;
                break;
            case 3:
                rb = 79.12 / 100;
                break;
            case 4:
                rb = 77.16 / 100;
                break;
            case 5:
                rb = 75.20 / 100;
                break;
            case 6:
                rb = 73.24 / 100;
                break;
            case 7:
                rb = 71.28 / 100;
                break;
            case 8:
                rb = 69.32 / 100;
                break;
            case 9:
                rb = 67.36 / 100;
                break;
            case 10:
                rb = 65.40 / 100;
                break;
            case 11:
                rb = 63.44 / 100;
                break;
            case 12:
                rb = 61.48 / 100;
                break;
            case 13:
                rb = 59.52 / 100;
                break;
            case 14:
                rb = 57.56 / 100;
                break;
            case 15:
                rb = 55.60 / 100;
                break;
            case 16:
                rb = 53.64 / 100;
                break;
            case 17:
                rb = 51.68 / 100;
                break;
            case 18:
                rb = 49.72 / 100;
                break;
            case 19:
                rb = 47.76 / 100;
                break;
            case 20:
                rb = 45.80 / 100;
                break;
            case 21:
                rb = 43.84 / 100;
                break;
            case 22:
                rb = 41.88 / 100;
                break;
            case 23:
                rb = 39.92 / 100;
                break;
            case 24:
                rb = 37.96 / 100;
                break;
            case 25:
                rb = 36.00 / 100;
                break;
            case 26:
                rb = 34.04 / 100;
                break;
            case 27:
                rb = 32.08 / 100;
                break;
            case 28:
                rb = 30.12 / 100;
                break;
            case 29:
                rb = 28.16 / 100;
                break;
            case 30:
                rb = 26.20 / 100;
                break;
        }
        var yhke;
        var ll;
        var zhbj;
        var zdhkll;
        zhbj = Math.round(dked * 10000 * rb * 100) / 100;
        if (dknx <= 5) 
        {
            ll = l1_5 / 12;
            zdhkll = 0.0378 / 12;
            var total_gjj = Math.pow(zdhkll + 1, dknx * 12);
            syhk = Math.ceil(dked * 10000 * zdhkll * total_gjj / (total_gjj - 1));
        } 
        else 
        {
            ll = l6_30 / 12;
            zdhkll = 0.0423 / 12;
            var total_gjj = Math.pow(zdhkll + 1, dknx * 12 - 1);
            syhk = Math.ceil((dked * 10000 - zhbj) * zdhkll * total_gjj / (total_gjj - 1) + zhbj * zdhkll);
        }
        sfksan.innerHTML = syhk;
        var zhyqbj = dked * 10000;
        var zchlx = 0;
        for (i = 1; i < dknx * 12; i++) 
        {
            zchlx += Math.round(zhyqbj * ll * 100) / 100;
            zhyqbj = Math.round((zhyqbj - (syhk - Math.round(zhyqbj * ll * 100) / 100)) * 100) / 100;
        }
        var sydkze = dked * 10000 - syhk;
        lx4.innerHTML=zhyqbj;
        lx5.innerHTML = Math.round(zhyqbj * ll * 100) / 100;
        zchlx += Math.round(zhyqbj * ll * 100) / 100;
        lx6.innerHTML = Math.round(zchlx * 100) / 100;
    }
}