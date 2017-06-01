<?php 
?>
<form class="auth-sidebar__form form-check reg js-reg">
    <div class="auth-sidebar__form-radios">
        <label>
            <input type="radio" name="buy" checked><i class="radio-ico"></i><span>Я покупаю</span>
        </label>
        <label>
            <input type="radio" name="buy"><i class="radio-ico"></i><span>Я продаю</span>
        </label>
    </div>
    <div class="auth-sidebar__form-brims">
        <label>
            <input type="email" placeholder="Email" name="email" class="form-control"><i class="fa fa-envelope-square"></i>
        </label>
        <label>
            <input type="tel" placeholder="Телефон" name="tel" class="form-control"><i class="fa fa-phone-square"></i>
        </label>
        <label>
            <input type="password" placeholder="Пароль" name="password" class="form-control"><i class="fa fa-lock"></i>
        </label>
    </div>
    <button type="submit" class="but but_green"><span>Зарегистрироваться</span><i class="ico"></i></button>
    <div class="auth-sidebar__enter reg"><span>Уже зарегистрированы?</span><a href="#">войти в систему</a></div>
</form>
