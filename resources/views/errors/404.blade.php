@php
  $errorBrandName = \App\Models\PlatformSetting::getBrandName();
  $errorBrandNameUpper = \App\Models\PlatformSetting::getBrandNameUpper();
  $errorBrandMonogram = mb_strtoupper(mb_substr(trim($errorBrandName), 0, 1, 'UTF-8'), 'UTF-8') ?: 'M';
@endphp
<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <meta name="theme-color" content="#faf7f3">
  <meta name="robots" content="noindex, follow">
  <meta name="application-name" content="{{ $errorBrandName }}">
  <meta name="description" content="Không tìm thấy trang bạn đang tìm. {{ $errorBrandName }} sẽ giúp bạn trở về với những câu chuyện yêu thương.">
  <title>404 – Tấm thiệp lạc đường | {{ $errorBrandName }}</title>
  @include('client.components.favicon')
  <style>
    /* MAY HEN / 404 — standalone HTML + CSS + JavaScript. No libraries, no CDN. */
    :root {
      --mayhen-error-404-background: #faf7f3;
      --mayhen-error-404-paper: #fffdfa;
      --mayhen-error-404-paper-deep: #f8f0ea;
      --mayhen-error-404-rose: #805052;
      --mayhen-error-404-rose-dark: #653d43;
      --mayhen-error-404-rose-light: #c79d9b;
      --mayhen-error-404-gold: #b58e6f;
      --mayhen-error-404-gold-light: #e3cbb3;
      --mayhen-error-404-ink: #403539;
      --mayhen-error-404-muted: #8b7975;
      --mayhen-error-404-border: #e8dad2;
      --mayhen-error-404-success: #3a7058;
      --mayhen-error-404-body-font: 'Segoe UI', Tahoma, Arial, sans-serif;
      --mayhen-error-404-title-font: Georgia, 'Times New Roman', serif;
      --mayhen-error-404-shadow: 0 28px 85px rgba(91, 59, 53, .085), 0 3px 16px rgba(91, 59, 53, .035);
    }
    *, *::before, *::after { box-sizing: border-box; }
    html { scroll-behavior: smooth; -webkit-text-size-adjust: 100%; }
    body { margin: 0; }
    button, input, select, textarea { font: inherit; }
    button { cursor: pointer; }
    a { color: inherit; }
    svg { display: block; }
    ::selection { background: #e9d0c6; color: var(--mayhen-error-404-rose-dark); }
    :focus-visible { outline: 3px solid var(--mayhen-error-404-gold); outline-offset: 4px; }

    .mayhen_error_404_skip {
      position: fixed; z-index: 100; top: -100px; left: 16px; padding: 12px 18px;
      border-radius: 9px; background: var(--mayhen-error-404-rose); color: #fff;
      text-decoration: none; font-size: 13px;
    }
    .mayhen_error_404_skip:focus { top: 14px; }
    .mayhen_error_404_page {
      position: relative; isolation: isolate; min-height: 100vh; min-height: 100dvh;
      overflow: hidden; padding: clamp(15px, 3.25vw, 46px);
      font-family: var(--mayhen-error-404-body-font); color: var(--mayhen-error-404-ink);
      background:
        radial-gradient(ellipse 44% 55% at -8% 0%, rgba(221, 188, 175, .24), transparent 95%),
        radial-gradient(ellipse 43% 46% at 105% 96%, rgba(218, 189, 169, .23), transparent 95%),
        var(--mayhen-error-404-background);
    }
    .mayhen_error_404_page::before {
      content: ''; position: absolute; inset: 0; z-index: -2; opacity: .17; pointer-events: none;
      background-image: radial-gradient(#bba69a .72px, transparent .85px);
      background-size: 22px 22px;
      mask-image: linear-gradient(to bottom, #000, transparent 66%);
    }
    .mayhen_error_404_orbit { position: absolute; width: 520px; aspect-ratio: 1; border: 1px solid rgba(181, 142, 111, .16); border-radius: 50%; z-index: -1; pointer-events: none; }
    .mayhen_error_404_orbit::before { content: ''; position: absolute; inset: 24px; border: 1px solid rgba(181, 142, 111, .13); border-radius: inherit; }
    .mayhen_error_404_orbit_top { left: -345px; top: -360px; }
    .mayhen_error_404_orbit_bottom { right: -360px; bottom: -370px; }

    .mayhen_error_404_shell {
      position: relative; width: min(1300px, 100%); margin-inline: auto;
      overflow: hidden; border: 1px solid #e7d9cf; border-radius: 23px;
      background: var(--mayhen-error-404-paper); box-shadow: var(--mayhen-error-404-shadow);
    }
    .mayhen_error_404_shell::before {
      content: ''; position: absolute; inset: 12px; z-index: 0; border: 1px solid rgba(190, 157, 128, .35);
      border-radius: 15px; pointer-events: none;
    }
    .mayhen_error_404_shell::after {
      content: ''; position: absolute; z-index: 0; left: 50%; top: 13px; transform: translateX(-50%);
      width: 102px; height: 8px; background: var(--mayhen-error-404-paper);
      border-top: 1px solid var(--mayhen-error-404-gold-light); pointer-events: none;
    }
    .mayhen_error_404_header, .mayhen_error_404_main, .mayhen_error_404_footer { position: relative; z-index: 1; }
    .mayhen_error_404_header {
      display: flex; align-items: center; justify-content: space-between; gap: 22px;
      padding: 31px clamp(31px, 4.6vw, 69px) 10px;
    }
    .mayhen_error_404_brand { display: inline-flex; align-items: center; gap: 11px; text-decoration: none; min-width: 0; }
    .mayhen_error_404_brand_mark { display: grid; place-items: center; width: 46px; height: 46px; flex: none; color: var(--mayhen-error-404-rose); }
    .mayhen_error_404_brand_mark svg { width: 44px; height: 44px; }
    .mayhen_error_404_brand_name { display: block; color: var(--mayhen-error-404-rose); font: 600 20px/1.1 var(--mayhen-error-404-title-font); letter-spacing: .125em; }
    .mayhen_error_404_brand_tag { display: block; margin-top: 6px; color: #a88b7e; font-size: 8px; font-weight: 700; letter-spacing: .21em; }
    .mayhen_error_404_nav { display: flex; align-items: center; gap: clamp(13px, 2.9vw, 36px); }
    .mayhen_error_404_nav_link { padding: 10px 0; color: #73625e; text-decoration: none; white-space: nowrap; font-size: 12px; font-weight: 600; transition: color .2s; }
    .mayhen_error_404_nav_link:hover { color: var(--mayhen-error-404-rose); }
    .mayhen_error_404_nav_cta { display: inline-flex; gap: 8px; align-items: center; justify-content: center; padding: 12px 16px; border: 1px solid #dbc8ba; border-radius: 9px; background: #fffaf5; color: var(--mayhen-error-404-rose); }
    .mayhen_error_404_nav_cta:hover { background: #f7ede6; border-color: var(--mayhen-error-404-gold); }
    .mayhen_error_404_nav_cta svg { width: 15px; height: 15px; }

    .mayhen_error_404_hero {
      display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, .95fr);
      align-items: center; gap: clamp(10px, 3vw, 48px);
      padding: 23px clamp(34px, 5.35vw, 76px) 18px;
    }
    .mayhen_error_404_copy { max-width: 560px; padding: 20px 0 24px; }
    .mayhen_error_404_eyebrow { display: inline-flex; align-items: center; gap: 11px; margin-bottom: 22px; color: var(--mayhen-error-404-rose); font-size: 10px; font-weight: 750; text-transform: uppercase; letter-spacing: .195em; }
    .mayhen_error_404_eyebrow_line { width: 31px; height: 1px; background: var(--mayhen-error-404-gold); }
    .mayhen_error_404_eyebrow_star { color: var(--mayhen-error-404-gold); font-size: 18px; font-weight: 400; }
    .mayhen_error_404_title { margin: 0; font: 400 clamp(41px, 4.2vw, 65px)/1.17 var(--mayhen-error-404-title-font); letter-spacing: -.052em; text-wrap: balance; }
    .mayhen_error_404_title em { color: var(--mayhen-error-404-rose); font-weight: 400; font-style: italic; }
    .mayhen_error_404_description { max-width: 450px; margin: 23px 0 0; color: #81716e; font-size: 14px; line-height: 1.94; }
    .mayhen_error_404_description strong { color: #655153; font-weight: 650; }
    .mayhen_error_404_hero_divider { display: flex; align-items: center; gap: 14px; width: min(320px, 100%); margin: 26px 0 26px; color: var(--mayhen-error-404-gold); }
    .mayhen_error_404_hero_divider::before, .mayhen_error_404_hero_divider::after { content: ''; flex: 1; height: 1px; background: #e4cfc0; }
    .mayhen_error_404_hero_divider svg { width: 32px; height: 18px; }
    .mayhen_error_404_actions { display: flex; flex-wrap: wrap; align-items: center; gap: 12px; }
    .mayhen_error_404_button {
      display: inline-flex; align-items: center; justify-content: center; gap: 11px;
      min-height: 51px; padding: 0 21px; border: 1px solid transparent; border-radius: 9px;
      font-size: 12px; line-height: 1.3; font-weight: 700; text-decoration: none;
      transition: background .2s, border-color .2s, box-shadow .2s, transform .2s;
    }
    .mayhen_error_404_button svg { width: 17px; height: 17px; flex: none; }
    .mayhen_error_404_button_primary { color: #fff; background: var(--mayhen-error-404-rose); border-color: var(--mayhen-error-404-rose); box-shadow: 0 11px 23px rgba(128, 80, 82, .17); }
    .mayhen_error_404_button_primary:hover { transform: translateY(-2px); background: var(--mayhen-error-404-rose-dark); box-shadow: 0 14px 29px rgba(128, 80, 82, .2); }
    .mayhen_error_404_button_secondary { color: var(--mayhen-error-404-rose); background: #fff; border-color: #e4cec3; }
    .mayhen_error_404_button_secondary:hover { transform: translateY(-2px); background: #fcf4ef; border-color: var(--mayhen-error-404-rose-light); }
    .mayhen_error_404_back { display: inline-flex; align-items: center; gap: 8px; margin-top: 17px; padding: 8px 2px; border: 0; color: var(--mayhen-error-404-rose); background: transparent; font-size: 12px; font-weight: 650; }
    .mayhen_error_404_back svg { width: 15px; height: 15px; }
    .mayhen_error_404_back:hover { text-decoration: underline; text-underline-offset: 4px; }

    /* Illustrated wedding invitation — entirely CSS + inline SVG */
    .mayhen_error_404_scene {
      position: relative; width: 100%; min-height: 515px; display: grid; place-items: center;
      overflow: visible; isolation: isolate;
    }
    .mayhen_error_404_scene::before { content: ''; position: absolute; width: min(520px, 110%); aspect-ratio: 1; border-radius: 50%; background: radial-gradient(circle, rgba(249, 222, 210, .77), rgba(246, 229, 221, .40) 57%, transparent 72%); }
    .mayhen_error_404_scene_circle { position: absolute; width: 440px; max-width: 103%; aspect-ratio: 1; border: 1px solid rgba(191, 157, 131, .22); border-radius: 50%; }
    .mayhen_error_404_scene_circle::after { content: ''; position: absolute; inset: 17px; border-radius: inherit; border: 1px solid rgba(191, 157, 131, .17); }
    .mayhen_error_404_scene_sparkle { position: absolute; z-index: 4; color: #b99278; font: 25px var(--mayhen-error-404-title-font); animation: mayhen_error_404_twinkle 4s ease-in-out infinite; }
    .mayhen_error_404_scene_sparkle_one { top: 47px; left: 24%; }
    .mayhen_error_404_scene_sparkle_two { right: 12%; top: 154px; font-size: 14px; animation-delay: 1s; }
    .mayhen_error_404_scene_sparkle_three { bottom: 73px; left: 12%; font-size: 17px; animation-delay: 2s; }
    .mayhen_error_404_flower { position: absolute; z-index: 4; color: #a48473; pointer-events: none; filter: drop-shadow(0 6px 5px rgba(108, 68, 61, .045)); }
    .mayhen_error_404_flower_left { width: 215px; height: 295px; left: -25px; bottom: 6px; transform: rotate(-25deg); }
    .mayhen_error_404_flower_right { width: 216px; height: 295px; right: -30px; top: 10px; transform: scaleX(-1) rotate(-26deg); }
    .mayhen_error_404_note_back {
      position: absolute; width: 268px; height: 365px; top: 66px; left: 50%; transform: translateX(-48%) rotate(9deg);
      border: 1px solid #dbc9b7; border-radius: 8px; background: linear-gradient(140deg, #fffdf9, #f5e8dc);
      box-shadow: 0 13px 30px rgba(102, 67, 54, .09);
    }
    .mayhen_error_404_note_back::before { content: ''; position: absolute; inset: 12px; border: 1px solid #e8dacd; border-radius: 4px; }
    .mayhen_error_404_note_back::after { content: 'M  ·  H'; position: absolute; top: 45px; left: 0; right: 0; color: #c9b4a1; text-align: center; letter-spacing: .5em; font: 14px var(--mayhen-error-404-title-font); }

    .mayhen_error_404_invitation {
      position: relative; z-index: 3; width: min(308px, 78%); min-height: 392px;
      transform: rotate(-7deg); padding: 22px; border: 1px solid #e1cdb9; border-radius: 6px;
      background: linear-gradient(160deg, #fffdfa 5%, #fff9f2 56%, #faf0e5 100%);
      box-shadow: 4px 22px 45px rgba(90, 60, 52, .18), 0 2px 8px rgba(90, 60, 52, .06);
      animation: mayhen_error_404_float 6s ease-in-out infinite;
    }
    .mayhen_error_404_invitation_border { min-height: 347px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 20px 12px; border: 1px solid #d7b99b; text-align: center; }
    .mayhen_error_404_invitation_ornament { color: #b48d73; font-size: 28px; line-height: 1; }
    .mayhen_error_404_invitation_top { margin: 12px 0 13px; color: #ad8b79; font-size: 9px; font-weight: 700; letter-spacing: .23em; }
    .mayhen_error_404_invitation_number { display: block; color: var(--mayhen-error-404-rose); font: 400 clamp(81px, 8vw, 117px)/.94 var(--mayhen-error-404-title-font); letter-spacing: -.095em; text-shadow: 2px 3px 0 rgba(189, 157, 133, .12); }
    .mayhen_error_404_invitation_rule { display: flex; align-items: center; gap: 10px; width: 88%; margin: 23px 0 15px; color: #b58e6f; font-size: 12px; }
    .mayhen_error_404_invitation_rule::before, .mayhen_error_404_invitation_rule::after { content: ''; height: 1px; background: #ddc5af; flex: 1; }
    .mayhen_error_404_invitation_caption { margin: 0; color: #71595a; font: italic 19px/1.25 var(--mayhen-error-404-title-font); }
    .mayhen_error_404_invitation_small { margin-top: 10px; color: #9f897f; font-size: 10px; line-height: 1.6; letter-spacing: .035em; }
    .mayhen_error_404_seal {
      position: absolute; z-index: 5; display: grid; place-items: center; width: 82px; height: 82px;
      right: 6%; bottom: 36px; border-radius: 50%; border: 5px double rgba(255, 226, 207, .35);
      color: #f3dac9; background: radial-gradient(circle at 30% 25%, #a16b70, #79484f 65%, #64353d);
      box-shadow: 0 7px 15px rgba(77, 45, 45, .25), inset 0 3px 8px rgba(255, 255, 255, .14);
      font: italic 35px var(--mayhen-error-404-title-font); transform: rotate(11deg);
    }
    .mayhen_error_404_scene_caption { position: absolute; z-index: 6; left: 4%; bottom: 26px; color: #98796d; font: italic 15px var(--mayhen-error-404-title-font); transform: rotate(-7deg); }

    .mayhen_error_404_assistance {
      margin: 3px clamp(32px, 5.35vw, 75px) 0; padding: 29px 31px 28px; border: 1px solid #eee1d9;
      border-radius: 14px; background: linear-gradient(105deg, #fcf7f2, #fffaf7 55%, #fffdf9);
    }
    .mayhen_error_404_assistance_top { display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; margin-bottom: 19px; }
    .mayhen_error_404_assistance_kicker { color: #a87b70; font-size: 9px; font-weight: 750; letter-spacing: .23em; text-transform: uppercase; }
    .mayhen_error_404_assistance_title { margin: 7px 0 0; font: 400 clamp(23px, 2.5vw, 30px)/1.25 var(--mayhen-error-404-title-font); letter-spacing: -.03em; }
    .mayhen_error_404_assistance_note { margin-bottom: 3px; color: #9c8781; font: italic 13px var(--mayhen-error-404-title-font); }
    .mayhen_error_404_tiles { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
    .mayhen_error_404_tile { display: flex; align-items: center; gap: 12px; min-width: 0; padding: 16px; border: 1px solid #eaddd5; border-radius: 10px; background: rgba(255, 255, 255, .84); text-decoration: none; transition: transform .2s, box-shadow .2s, border-color .2s; }
    .mayhen_error_404_tile:hover { transform: translateY(-3px); border-color: #d9bca7; box-shadow: 0 10px 20px rgba(107, 70, 60, .07); }
    .mayhen_error_404_tile_icon { display: grid; place-items: center; flex: none; width: 43px; height: 43px; border: 1px solid #e8d9ce; border-radius: 10px; background: #fcf4ee; color: var(--mayhen-error-404-rose); }
    .mayhen_error_404_tile_icon svg { width: 22px; height: 22px; }
    .mayhen_error_404_tile_text { min-width: 0; flex: 1; }
    .mayhen_error_404_tile_title { display: block; color: #5d494b; font-size: 12px; font-weight: 700; }
    .mayhen_error_404_tile_subtitle { display: block; margin-top: 5px; color: #95817b; font-size: 11px; line-height: 1.45; }
    .mayhen_error_404_tile_arrow { width: 15px; height: 15px; flex: none; color: #a68b7d; }
    .mayhen_error_404_search_section { display: grid; grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr); align-items: center; gap: 29px; margin: 26px clamp(32px, 5.35vw, 75px) 30px; padding: 0 0 0 3px; }
    .mayhen_error_404_search_intro { display: flex; align-items: flex-start; gap: 13px; }
    .mayhen_error_404_search_icon { width: 36px; height: 36px; flex: none; color: #aa8c7d; margin-top: 2px; }
    .mayhen_error_404_search_title { margin: 0 0 6px; font: 400 23px/1.3 var(--mayhen-error-404-title-font); }
    .mayhen_error_404_search_description { margin: 0; color: #96817c; font-size: 12px; line-height: 1.65; }
    .mayhen_error_404_search_box { position: relative; display: flex; gap: 8px; padding: 6px 6px 6px 17px; border: 1px solid #dfcfc7; border-radius: 11px; background: #fff; transition: border-color .2s, box-shadow .2s; }
    .mayhen_error_404_search_box:focus-within { border-color: var(--mayhen-error-404-rose); box-shadow: 0 0 0 4px rgba(128, 80, 82, .08); }
    .mayhen_error_404_search_input { width: 100%; min-width: 0; height: 40px; border: 0; outline: 0; color: var(--mayhen-error-404-ink); background: transparent; font-size: 13px; }
    .mayhen_error_404_search_input::placeholder { color: #b29e95; }
    .mayhen_error_404_search_button { display: inline-flex; align-items: center; gap: 8px; min-height: 42px; padding: 0 17px; border: 0; border-radius: 7px; background: var(--mayhen-error-404-rose); color: #fff; font-size: 12px; font-weight: 700; white-space: nowrap; }
    .mayhen_error_404_search_button:hover { background: var(--mayhen-error-404-rose-dark); }
    .mayhen_error_404_search_button svg { width: 16px; height: 16px; }
    .mayhen_error_404_search_error { margin: 6px 0 0; min-height: 16px; color: #a4414d; font-size: 11px; }
    .mayhen_error_404_footer { display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 18px; margin: 0 clamp(32px, 5.35vw, 75px); padding: 20px 0 26px; border-top: 1px solid #eee2da; }
    .mayhen_error_404_footer_note { color: #a18b85; font: italic 13px var(--mayhen-error-404-title-font); }
    .mayhen_error_404_footer_nav { display: flex; flex-wrap: wrap; align-items: center; gap: 19px; }
    .mayhen_error_404_footer_link { padding: 4px 0; border: 0; color: #80665e; background: transparent; text-decoration: none; font-size: 11px; font-weight: 650; }
    .mayhen_error_404_footer_link:hover { color: var(--mayhen-error-404-rose); text-decoration: underline; text-underline-offset: 4px; }
    .mayhen_error_404_footer_dot { color: var(--mayhen-error-404-gold); font-size: 13px; }

    /* Report a broken link in an accessible native dialog. */
    .mayhen_error_404_dialog { width: min(480px, calc(100% - 30px)); max-height: min(740px, calc(100dvh - 24px)); overflow-y: auto; padding: 0; border: 1px solid #e1cec0; border-radius: 16px; background: var(--mayhen-error-404-paper); color: var(--mayhen-error-404-ink); box-shadow: 0 25px 80px rgba(57, 32, 31, .23); }
    .mayhen_error_404_dialog::backdrop { background: rgba(58, 40, 44, .56); backdrop-filter: blur(4px); }
    .mayhen_error_404_dialog_inner { padding: 29px clamp(22px, 5vw, 36px) 29px; }
    .mayhen_error_404_dialog_top { display: flex; gap: 16px; justify-content: space-between; align-items: flex-start; }
    .mayhen_error_404_dialog_kicker { margin: 0 0 9px; color: var(--mayhen-error-404-rose); font-size: 10px; text-transform: uppercase; font-weight: 750; letter-spacing: .18em; }
    .mayhen_error_404_dialog_title { margin: 0; font: 400 29px/1.25 var(--mayhen-error-404-title-font); }
    .mayhen_error_404_dialog_close { display: grid; place-items: center; flex: none; width: 36px; height: 36px; border: 1px solid #eadbd4; border-radius: 9px; background: #fff9f5; color: var(--mayhen-error-404-rose); }
    .mayhen_error_404_dialog_close svg { width: 18px; height: 18px; }
    .mayhen_error_404_dialog_description { margin: 13px 0 21px; color: #877670; font-size: 12px; line-height: 1.75; }
    .mayhen_error_404_dialog_label { display: block; margin: 15px 0 7px; font-size: 12px; font-weight: 650; color: #624e50; }
    .mayhen_error_404_dialog_field { width: 100%; padding: 12px 13px; border: 1px solid #e5d7ce; border-radius: 8px; outline: 0; background: #fff; color: var(--mayhen-error-404-ink); font-size: 13px; }
    .mayhen_error_404_dialog_field:focus { border-color: var(--mayhen-error-404-rose); box-shadow: 0 0 0 3px rgba(128, 80, 82, .1); }
    .mayhen_error_404_dialog_textarea { resize: vertical; min-height: 96px; }
    .mayhen_error_404_dialog_url { overflow-wrap: anywhere; color: #96817a; font-size: 11px; line-height: 1.65; }
    .mayhen_error_404_dialog_actions { display: flex; gap: 9px; flex-wrap: wrap; justify-content: flex-end; margin-top: 21px; }
    .mayhen_error_404_toast { position: fixed; z-index: 120; left: 50%; bottom: 25px; max-width: min(450px, calc(100% - 35px)); padding: 13px 19px; border: 1px solid #d8c5b8; border-radius: 9px; background: #fffaf6; color: #664b4c; box-shadow: 0 12px 35px rgba(69, 44, 39, .16); font-size: 12px; line-height: 1.5; text-align: center; transform: translate(-50%, 20px); opacity: 0; pointer-events: none; transition: transform .22s, opacity .22s; }
    .mayhen_error_404_toast[data-open='true'] { opacity: 1; transform: translate(-50%, 0); }

    @keyframes mayhen_error_404_float { 0%, 100% { transform: translateY(0) rotate(-7deg); } 50% { transform: translateY(-9px) rotate(-7deg); } }
    @keyframes mayhen_error_404_twinkle { 0%, 100% { opacity: .48; transform: scale(.85); } 50% { opacity: 1; transform: scale(1.15); } }
    @media (max-width: 1090px) {
      .mayhen_error_404_hero { grid-template-columns: minmax(0, 1fr) minmax(0, .9fr); gap: 6px; }
      .mayhen_error_404_scene { min-height: 450px; }
      .mayhen_error_404_scene_circle { width: 380px; }
      .mayhen_error_404_flower_left { left: -32px; width: 175px; }
      .mayhen_error_404_flower_right { right: -32px; width: 175px; }
      .mayhen_error_404_note_back { transform: translateX(-48%) rotate(9deg) scale(.9); top: 40px; }
      .mayhen_error_404_invitation { transform: rotate(-7deg) scale(.9); animation: none; }
      .mayhen_error_404_seal { right: 1%; }
      .mayhen_error_404_assistance { padding: 23px; }
      .mayhen_error_404_tile { padding: 12px; gap: 9px; }
      .mayhen_error_404_tile_icon { width: 37px; height: 37px; }
      .mayhen_error_404_tile_arrow { display: none; }
    }
    @media (max-width: 760px) {
      .mayhen_error_404_page { padding: 13px; }
      .mayhen_error_404_shell { border-radius: 18px; }
      .mayhen_error_404_shell::before { inset: 8px; border-radius: 11px; }
      .mayhen_error_404_header { padding: 24px 27px 11px; }
      .mayhen_error_404_nav { gap: 13px; }
      .mayhen_error_404_nav_link:not(.mayhen_error_404_nav_cta) { display: none; }
      .mayhen_error_404_hero { grid-template-columns: 1fr; padding: 22px 29px 0; gap: 0; }
      .mayhen_error_404_copy { max-width: 620px; padding: 12px 0 0; }
      .mayhen_error_404_title { font-size: clamp(42px, 7.5vw, 59px); }
      .mayhen_error_404_description { max-width: 540px; }
      .mayhen_error_404_scene { width: min(470px, 100%); min-height: 410px; margin: 0 auto -8px; }
      .mayhen_error_404_scene_circle { width: 370px; }
      .mayhen_error_404_invitation { transform: rotate(-7deg) scale(.88); }
      .mayhen_error_404_scene_caption { left: 7%; bottom: 11px; }
      .mayhen_error_404_assistance { margin: 12px 28px 0; }
      .mayhen_error_404_tiles { grid-template-columns: 1fr; }
      .mayhen_error_404_tile { padding: 14px; }
      .mayhen_error_404_tile_arrow { display: block; }
      .mayhen_error_404_search_section { grid-template-columns: 1fr; gap: 17px; margin: 27px 28px; }
      .mayhen_error_404_footer { margin: 0 28px; }
    }
    @media (max-width: 430px) {
      .mayhen_error_404_page { padding: 8px; }
      .mayhen_error_404_shell { border-radius: 13px; }
      .mayhen_error_404_shell::before { inset: 6px; border-radius: 9px; }
      .mayhen_error_404_header { padding: 22px 20px 11px; }
      .mayhen_error_404_brand_mark, .mayhen_error_404_brand_mark svg { width: 38px; height: 38px; }
      .mayhen_error_404_brand_name { font-size: 17px; }
      .mayhen_error_404_brand_tag { font-size: 7px; }
      .mayhen_error_404_nav_cta { padding: 10px 11px; font-size: 11px; }
      .mayhen_error_404_hero { padding: 17px 22px 0; }
      .mayhen_error_404_eyebrow { font-size: 9px; letter-spacing: .11em; gap: 9px; margin-bottom: 17px; }
      .mayhen_error_404_eyebrow_line { width: 20px; }
      .mayhen_error_404_title { font-size: clamp(38px, 10vw, 47px); }
      .mayhen_error_404_description { margin-top: 16px; font-size: 13px; line-height: 1.8; }
      .mayhen_error_404_hero_divider { margin: 19px 0; }
      .mayhen_error_404_actions { display: grid; grid-template-columns: 1fr 1fr; gap: 9px; }
      .mayhen_error_404_button { min-height: 48px; padding: 0 9px; gap: 7px; font-size: 11px; }
      .mayhen_error_404_button svg { width: 16px; height: 16px; }
      .mayhen_error_404_back { margin-top: 11px; }
      .mayhen_error_404_scene { min-height: 343px; margin-top: 3px; }
      .mayhen_error_404_scene_circle { width: 295px; }
      .mayhen_error_404_note_back { top: -6px; transform: translateX(-48%) rotate(9deg) scale(.7); }
      .mayhen_error_404_invitation { transform: rotate(-7deg) scale(.73); animation: none; }
      .mayhen_error_404_flower_left { width: 138px; height: 212px; left: -20px; bottom: -8px; }
      .mayhen_error_404_flower_right { width: 145px; height: 212px; right: -23px; top: 2px; }
      .mayhen_error_404_seal { width: 59px; height: 59px; right: 4%; bottom: 26px; font-size: 26px; }
      .mayhen_error_404_scene_caption { left: 0; bottom: 2px; font-size: 12px; }
      .mayhen_error_404_scene_sparkle_one { top: 9px; }
      .mayhen_error_404_scene_sparkle_three { bottom: 35px; }
      .mayhen_error_404_assistance { margin: 9px 20px 0; padding: 20px 15px; }
      .mayhen_error_404_assistance_top { display: block; margin-bottom: 17px; }
      .mayhen_error_404_assistance_note { display: none; }
      .mayhen_error_404_assistance_title { font-size: 24px; }
      .mayhen_error_404_tile { padding: 12px; }
      .mayhen_error_404_search_section { margin: 24px 20px; gap: 12px; }
      .mayhen_error_404_search_title { font-size: 21px; }
      .mayhen_error_404_search_icon { width: 30px; height: 30px; }
      .mayhen_error_404_search_box { padding-left: 9px; }
      .mayhen_error_404_search_input { font-size: 12px; }
      .mayhen_error_404_search_button { padding: 0 12px; }
      .mayhen_error_404_footer { margin: 0 20px; gap: 13px; padding-bottom: 24px; }
      .mayhen_error_404_footer_nav { gap: 11px; }
      .mayhen_error_404_footer_note { font-size: 12px; }
    }
    @media (max-width: 350px) {
      .mayhen_error_404_header { gap: 7px; }
      .mayhen_error_404_brand { gap: 6px; }
      .mayhen_error_404_brand_name { white-space: nowrap; font-size: 16px; letter-spacing: .075em; }
      .mayhen_error_404_brand_tag { white-space: nowrap; font-size: 6.5px; letter-spacing: .15em; }
      .mayhen_error_404_nav_cta { padding: 9px 7px; font-size: 10px; }
      .mayhen_error_404_nav_cta svg { display: none; }
      .mayhen_error_404_title { font-size: 37px; }
      .mayhen_error_404_button { font-size: 10px; }
      .mayhen_error_404_assistance { margin-inline: 16px; }
      .mayhen_error_404_search_section, .mayhen_error_404_footer { margin-inline: 16px; }
    }
    @media (prefers-reduced-motion: reduce) {
      html { scroll-behavior: auto; }
      *, *::before, *::after { animation-duration: .01ms !important; animation-iteration-count: 1 !important; transition-duration: .01ms !important; }
    }
  </style>
</head>
<body>
<a class="mayhen_error_404_skip" href="#mayhen_error_404_main">Bỏ qua phần điều hướng</a>
<div class="mayhen_error_404_page">
  <span class="mayhen_error_404_orbit mayhen_error_404_orbit_top" aria-hidden="true"></span>
  <span class="mayhen_error_404_orbit mayhen_error_404_orbit_bottom" aria-hidden="true"></span>
  <div class="mayhen_error_404_shell">
    <header class="mayhen_error_404_header">
      <a class="mayhen_error_404_brand" href="{{ route('home') }}" data-mayhen-route="home" aria-label="{{ $errorBrandName }} – về trang chủ">
        <span class="mayhen_error_404_brand_mark" aria-hidden="true">
          <svg viewBox="0 0 48 48" fill="none"><path d="M24 41C18 34 6 28 6 18c0-6 4-10 10-10 4 0 7 2 8 6 2-4 5-6 9-6 6 0 10 4 10 10 0 10-12 16-19 23Z" stroke="currentColor" stroke-width="1.5"/><path d="M24 37C19 31 11 26 11 19" stroke="currentColor" opacity=".5"/><path d="M16 13c2 0 4 1 5 3M32 13c-2 0-4 1-5 3" stroke="currentColor" stroke-linecap="round"/><path d="M24 5v5M8 6l3 3M40 6l-3 3" stroke="#b58e6f" stroke-linecap="round"/></svg>
        </span>
        <span><span class="mayhen_error_404_brand_name">{{ $errorBrandNameUpper }}</span><span class="mayhen_error_404_brand_tag">WHERE LOVE BEGINS</span></span>
      </a>
      <nav class="mayhen_error_404_nav" aria-label="Điều hướng chính">
        <a class="mayhen_error_404_nav_link" href="{{ route('home') }}" data-mayhen-route="home">Trang chủ</a>
        <a class="mayhen_error_404_nav_link" href="{{ route('templates.index') }}" data-mayhen-route="templates">Mẫu thiệp</a>
        <a class="mayhen_error_404_nav_link mayhen_error_404_nav_cta" href="{{ route('invitation.create') }}" data-mayhen-route="create">
          Tạo thiệp của bạn <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </a>
      </nav>
    </header>

    <main class="mayhen_error_404_main" id="mayhen_error_404_main">
      <section class="mayhen_error_404_hero" aria-labelledby="mayhen_error_404_title">
        <div class="mayhen_error_404_copy">
          <div class="mayhen_error_404_eyebrow"><span class="mayhen_error_404_eyebrow_line"></span><span class="mayhen_error_404_eyebrow_star">✧</span> LẠC BƯỚC GIỮA NHỮNG LỜI HẸN · 404</div>
          <h1 class="mayhen_error_404_title" id="mayhen_error_404_title">Ôi, tấm thiệp <em>đi lạc</em> mất rồi!</h1>
          <p class="mayhen_error_404_description">Có lẽ con đường bạn vừa ghé đã đổi tên, hoặc tấm thiệp nhỏ chưa kịp đến nơi. Lạc bước giữa những lời hẹn — <strong>đừng lo nhé,</strong> những câu chuyện đẹp vẫn đang chờ bạn ở {{ $errorBrandName }}.</p>
          <div class="mayhen_error_404_hero_divider" aria-hidden="true"><svg viewBox="0 0 36 18" fill="none" stroke="currentColor" stroke-width="1.2"><path d="M18 15C12 8 7 2 3 5c-4 4 6 12 15 10Zm0 0C24 8 29 2 33 5c4 4-6 12-15 10Z"/><path d="M18 15V3"/></svg></div>
          <div class="mayhen_error_404_actions">
            <a class="mayhen_error_404_button mayhen_error_404_button_primary" href="{{ route('home') }}" data-mayhen-route="home">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m3 11 9-8 9 8M5 10v11h14V10M9 21v-7h6v7"/></svg> Về Trang Chủ
            </a>
            <a class="mayhen_error_404_button mayhen_error_404_button_secondary" href="{{ route('templates.index') }}" data-mayhen-route="templates">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="5" width="18" height="15" rx="2"/><path d="m3 8 9 7 9-7M7 3h10"/></svg> Khám Phá Mẫu Thiệp
            </a>
          </div>
          <button type="button" class="mayhen_error_404_back" id="mayhen_error_404_back"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><path d="m14 6-6 6 6 6M8 12h12"/></svg> Quay lại trang trước</button>
        </div>
        <div class="mayhen_error_404_scene" aria-hidden="true">
          <div class="mayhen_error_404_scene_circle"></div>
          <span class="mayhen_error_404_scene_sparkle mayhen_error_404_scene_sparkle_one">✧</span>
          <span class="mayhen_error_404_scene_sparkle mayhen_error_404_scene_sparkle_two">✳</span>
          <span class="mayhen_error_404_scene_sparkle mayhen_error_404_scene_sparkle_three">✧</span>
          <svg class="mayhen_error_404_flower mayhen_error_404_flower_left" viewBox="0 0 220 310" fill="none" stroke="currentColor" stroke-width="1.55" stroke-linecap="round" stroke-linejoin="round">
            <path d="M85 290C116 220 115 169 102 112M101 190C64 152 43 127 39 88M103 169c34-40 57-66 64-102M98 239c-36-21-58-40-71-67"/>
            <path d="M110 163c-17-31-36-30-36-30 1 20 13 35 36 30Zm-13 51c26-27 45-26 45-26-5 25-20 35-45 26Zm-34-68c-34 1-42-23-42-23 26-5 42 3 42 23Zm59-23c17-28 39-25 39-25-5 21-18 31-39 25Zm-51 117c-18-30-40-25-40-25 8 23 19 30 40 25Z" fill="#c0a594" fill-opacity=".16"/>
            <path d="M102 112C91 99 82 83 93 72c12-11 20 9 20 9s8-21 20-11c12 11-1 27-14 39M39 88C24 84 8 74 14 62c7-12 22 3 22 3s-4-20 11-20 13 20 7 35M168 68c-12-10-23-25-12-36 12-10 23 6 23 6s1-19 16-16c15 4 7 22-7 37M27 173C12 163 1 146 12 135c12-9 20 8 20 8s4-18 18-14c14 4 7 21-6 34" stroke="#a87f70"/>
            <path d="M101 110c-10-12-11-20-6-28M37 88c-6-12-7-17-6-25M169 67c-5-11-5-16-1-24" stroke="#c49f8b"/>
            <circle cx="113" cy="94" r="4" fill="#bf9384" fill-opacity=".4"/><circle cx="39" cy="75" r="3" fill="#bf9384" fill-opacity=".4"/><circle cx="175" cy="48" r="3" fill="#bf9384" fill-opacity=".4"/>
            <path d="M66 277c-18-7-30-21-33-37M118 266c15-13 29-19 40-17" stroke="#b79581"/>
          </svg>
          <svg class="mayhen_error_404_flower mayhen_error_404_flower_right" viewBox="0 0 220 310" fill="none" stroke="currentColor" stroke-width="1.55" stroke-linecap="round" stroke-linejoin="round">
            <path d="M85 290C116 220 115 169 102 112M101 190C64 152 43 127 39 88M103 169c34-40 57-66 64-102M98 239c-36-21-58-40-71-67"/>
            <path d="M110 163c-17-31-36-30-36-30 1 20 13 35 36 30Zm-13 51c26-27 45-26 45-26-5 25-20 35-45 26Zm-34-68c-34 1-42-23-42-23 26-5 42 3 42 23Zm59-23c17-28 39-25 39-25-5 21-18 31-39 25Zm-51 117c-18-30-40-25-40-25 8 23 19 30 40 25Z" fill="#c0a594" fill-opacity=".16"/>
            <path d="M102 112C91 99 82 83 93 72c12-11 20 9 20 9s8-21 20-11c12 11-1 27-14 39M39 88C24 84 8 74 14 62c7-12 22 3 22 3s-4-20 11-20 13 20 7 35M168 68c-12-10-23-25-12-36 12-10 23 6 23 6s1-19 16-16c15 4 7 22-7 37M27 173C12 163 1 146 12 135c12-9 20 8 20 8s4-18 18-14c14 4 7 21-6 34" stroke="#a87f70"/>
            <path d="M101 110c-10-12-11-20-6-28M37 88c-6-12-7-17-6-25M169 67c-5-11-5-16-1-24" stroke="#c49f8b"/>
            <circle cx="113" cy="94" r="4" fill="#bf9384" fill-opacity=".4"/><circle cx="39" cy="75" r="3" fill="#bf9384" fill-opacity=".4"/><circle cx="175" cy="48" r="3" fill="#bf9384" fill-opacity=".4"/>
            <path d="M66 277c-18-7-30-21-33-37M118 266c15-13 29-19 40-17" stroke="#b79581"/>
          </svg>
          <div class="mayhen_error_404_note_back"></div>
          <div class="mayhen_error_404_invitation"><div class="mayhen_error_404_invitation_border">
            <span class="mayhen_error_404_invitation_ornament">❦</span>
            <span class="mayhen_error_404_invitation_top">A LITTLE DETOUR OF LOVE</span>
            <span class="mayhen_error_404_invitation_number">404</span>
            <span class="mayhen_error_404_invitation_rule">✧</span>
            <p class="mayhen_error_404_invitation_caption">Lost in love</p>
            <span class="mayhen_error_404_invitation_small">Not lost forever, just a little detour.</span>
          </div></div>
          <span class="mayhen_error_404_seal">{{ $errorBrandMonogram }}</span>
          <span class="mayhen_error_404_scene_caption">with love, {{ $errorBrandName }} ♡</span>
        </div>
      </section>

      <section class="mayhen_error_404_assistance" aria-labelledby="mayhen_error_404_assistance_title">
        <div class="mayhen_error_404_assistance_top"><div><div class="mayhen_error_404_assistance_kicker">THAY VÌ DỪNG LẠI</div><h2 class="mayhen_error_404_assistance_title" id="mayhen_error_404_assistance_title">Một điểm đến đẹp khác dành cho bạn</h2></div><span class="mayhen_error_404_assistance_note">Choose where your story continues.</span></div>
        <div class="mayhen_error_404_tiles">
          <a class="mayhen_error_404_tile" href="{{ route('templates.index') }}" data-mayhen-route="templates"><span class="mayhen_error_404_tile_icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.55" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="4" width="18" height="16" rx="2"/><path d="m4 8 8 7 8-7M8 2h8"/></svg></span><span class="mayhen_error_404_tile_text"><span class="mayhen_error_404_tile_title">Kho mẫu thiệp</span><span class="mayhen_error_404_tile_subtitle">Chọn phong cách của riêng bạn</span></span><svg class="mayhen_error_404_tile_arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a>
          <a class="mayhen_error_404_tile" href="{{ route('invitation.create') }}" data-mayhen-route="create"><span class="mayhen_error_404_tile_icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 20s-8-4.8-8-10.2a4.8 4.8 0 0 1 8-3.5 4.8 4.8 0 0 1 8 3.5C20 15.2 12 20 12 20Z"/><path d="m9 12 2 2 4-4"/></svg></span><span class="mayhen_error_404_tile_text"><span class="mayhen_error_404_tile_title">Tạo thiệp mới</span><span class="mayhen_error_404_tile_subtitle">Bắt đầu câu chuyện tình yêu</span></span><svg class="mayhen_error_404_tile_arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a>
          <a class="mayhen_error_404_tile" href="{{ route('docs.index') }}" data-mayhen-route="help"><span class="mayhen_error_404_tile_icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M9.5 9a2.6 2.6 0 0 1 5 1c0 2-2.5 2.3-2.5 4M12 17h.01"/></svg></span><span class="mayhen_error_404_tile_text"><span class="mayhen_error_404_tile_title">Cần trợ giúp?</span><span class="mayhen_error_404_tile_subtitle">{{ $errorBrandName }} luôn sẵn lòng hỗ trợ</span></span><svg class="mayhen_error_404_tile_arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg></a>
        </div>
      </section>

      <section class="mayhen_error_404_search_section" aria-labelledby="mayhen_error_404_search_title">
        <div class="mayhen_error_404_search_intro"><svg class="mayhen_error_404_search_icon" viewBox="0 0 36 36" fill="none" stroke="currentColor" stroke-width="1.35" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="16" cy="16" r="9"/><path d="m22.5 22.5 8 8M13 16h6M16 13v6"/><path d="M26 4v5M23.5 6.5h5"/></svg><div><h2 class="mayhen_error_404_search_title" id="mayhen_error_404_search_title">Tìm lại điều bạn đang kiếm</h2><p class="mayhen_error_404_search_description">Thử tìm theo tên mẫu thiệp, chủ đề hoặc từ khóa bạn nhớ.</p></div></div>
        <form class="mayhen_error_404_search_form" id="mayhen_error_404_search_form" action="{{ route('templates.index') }}" method="GET" role="search" novalidate>
          <div class="mayhen_error_404_search_box"><input class="mayhen_error_404_search_input" id="mayhen_error_404_search_input" type="search" name="q" placeholder="Ví dụ: thiệp cưới tối giản..." aria-label="Từ khóa tìm kiếm" aria-describedby="mayhen_error_404_search_error" maxlength="120"><button type="submit" class="mayhen_error_404_search_button"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/></svg> Tìm kiếm</button></div>
          <p class="mayhen_error_404_search_error" id="mayhen_error_404_search_error" role="status"></p>
        </form>
      </section>
    </main>
    <footer class="mayhen_error_404_footer"><span class="mayhen_error_404_footer_note">{{ $errorBrandName }} — dù đi lạc, tình yêu vẫn tìm thấy đường về.</span><div class="mayhen_error_404_footer_nav"><a class="mayhen_error_404_footer_link" href="{{ route('contact') }}" data-mayhen-route="contact">Liên hệ</a><span class="mayhen_error_404_footer_dot">✧</span><button class="mayhen_error_404_footer_link" type="button" id="mayhen_error_404_report_open">Báo liên kết bị lỗi</button><span class="mayhen_error_404_footer_dot">✧</span><a class="mayhen_error_404_footer_link" href="{{ route('docs.index') }}" data-mayhen-route="help">Trung tâm hỗ trợ</a></div></footer>
  </div>
</div>

<dialog class="mayhen_error_404_dialog" id="mayhen_error_404_dialog" aria-labelledby="mayhen_error_404_dialog_title">
  <div class="mayhen_error_404_dialog_inner"><div class="mayhen_error_404_dialog_top"><div><p class="mayhen_error_404_dialog_kicker">MỘT LỜI NHẮN NHỎ</p><h2 class="mayhen_error_404_dialog_title" id="mayhen_error_404_dialog_title">Giúp {{ $errorBrandName }} tìm lại đường</h2></div><button class="mayhen_error_404_dialog_close" id="mayhen_error_404_report_close" type="button" aria-label="Đóng"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M5 5l14 14M19 5 5 19"/></svg></button></div>
    <p class="mayhen_error_404_dialog_description">Cảm ơn bạn đã giúp phát hiện liên kết chưa đúng. Bạn có thể mô tả một chút để đội ngũ {{ $errorBrandName }} kiểm tra dễ hơn.</p>
    <form id="mayhen_error_404_report_form">
      <label class="mayhen_error_404_dialog_label" for="mayhen_error_404_report_reason">Vấn đề bạn gặp phải</label>
      <select class="mayhen_error_404_dialog_field" id="mayhen_error_404_report_reason"><option value="Đường dẫn không tồn tại">Đường dẫn không tồn tại</option><option value="Liên kết trên website bị lỗi">Liên kết trên website bị lỗi</option><option value="Không tìm thấy mẫu thiệp">Không tìm thấy mẫu thiệp</option><option value="Vấn đề khác">Vấn đề khác</option></select>
      <label class="mayhen_error_404_dialog_label" for="mayhen_error_404_report_note">Mô tả thêm (không bắt buộc)</label>
      <textarea class="mayhen_error_404_dialog_field mayhen_error_404_dialog_textarea" id="mayhen_error_404_report_note" maxlength="700" placeholder="Bạn vừa nhấn vào đâu trước khi tới trang này?"></textarea>
      <p class="mayhen_error_404_dialog_url" id="mayhen_error_404_report_url"></p>
      <div class="mayhen_error_404_dialog_actions"><button class="mayhen_error_404_button mayhen_error_404_button_secondary" type="button" id="mayhen_error_404_report_cancel">Để sau</button><button class="mayhen_error_404_button mayhen_error_404_button_primary" type="submit" id="mayhen_error_404_report_submit">Sao chép nội dung báo lỗi</button></div>
    </form>
  </div>
</dialog>
<div class="mayhen_error_404_toast" id="mayhen_error_404_toast" role="status" aria-live="polite" aria-atomic="true"></div>

<script>
  (() => {
    'use strict';
    /** CẤU HÌNH: đồng bộ đường dẫn route chuẩn Laravel */
    const MAYHEN_ERROR_404_CONFIG = {
      home: '{{ route('home') }}',
      templates: '{{ route('templates.index') }}',
      create: '{{ route('invitation.create') }}',
      help: '{{ route('docs.index') }}',
      contact: '{{ route('contact') }}',
      search: '{{ route('templates.index') }}',
      searchParam: 'q',
      supportEmail: 'hotro@mayhen.vn',
      brandName: @json($errorBrandName),
      brandNameUpper: @json($errorBrandNameUpper)
    };

    const get = (id) => document.getElementById(id);
    const home = MAYHEN_ERROR_404_CONFIG.home;

    // Sync all navigation links to the central configuration.
    document.querySelectorAll('[data-mayhen-route]').forEach((link) => {
      const key = link.getAttribute('data-mayhen-route');
      if (Object.prototype.hasOwnProperty.call(MAYHEN_ERROR_404_CONFIG, key)) {
        link.href = MAYHEN_ERROR_404_CONFIG[key];
      }
    });

    get('mayhen_error_404_back').addEventListener('click', () => {
      try {
        const previous = document.referrer ? new URL(document.referrer) : null;
        if (window.history.length > 1 && previous && previous.origin === window.location.origin) {
          window.history.back();
          return;
        }
      } catch (_) { /* Fallback home */ }
      window.location.assign(home);
    });

    const searchForm = get('mayhen_error_404_search_form');
    const searchInput = get('mayhen_error_404_search_input');
    const searchError = get('mayhen_error_404_search_error');
    searchInput.addEventListener('input', () => { searchError.textContent = ''; searchInput.removeAttribute('aria-invalid'); });
    searchForm.addEventListener('submit', (event) => {
      event.preventDefault();
      const query = searchInput.value.trim();
      if (!query) {
        searchError.textContent = `Bạn nhập từ khóa để ${MAYHEN_ERROR_404_CONFIG.brandName} tìm giúp nhé.`;
        searchInput.setAttribute('aria-invalid', 'true');
        searchInput.focus();
        return;
      }
      const destination = new URL(MAYHEN_ERROR_404_CONFIG.search, window.location.href);
      destination.searchParams.set(MAYHEN_ERROR_404_CONFIG.searchParam, query);
      window.location.assign(destination.href);
    });

    // Handy shortcut: press '/' to focus search (when not typing in a field).
    document.addEventListener('keydown', (event) => {
      const target = event.target;
      const typing = target instanceof HTMLElement && (target.matches('input, textarea, select') || target.isContentEditable);
      if (event.key === '/' && !event.ctrlKey && !event.metaKey && !event.altKey && !typing && !get('mayhen_error_404_dialog').open) {
        event.preventDefault();
        searchInput.focus();
        searchInput.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth', block: 'center' });
      }
    });

    const dialog = get('mayhen_error_404_dialog');
    const toast = get('mayhen_error_404_toast');
    let toastTimeout;
    const showToast = (message) => {
      toast.textContent = message;
      toast.dataset.open = 'true';
      window.clearTimeout(toastTimeout);
      toastTimeout = window.setTimeout(() => { toast.dataset.open = 'false'; }, 4000);
    };
    const closeDialog = () => { if (dialog.open) dialog.close(); };
    get('mayhen_error_404_report_open').addEventListener('click', () => {
      get('mayhen_error_404_report_url').textContent = `Đường dẫn gặp lỗi: ${window.location.href}`;
      get('mayhen_error_404_report_submit').textContent = MAYHEN_ERROR_404_CONFIG.supportEmail ? 'Soạn email báo lỗi' : 'Sao chép nội dung báo lỗi';
      if (typeof dialog.showModal === 'function') dialog.showModal();
      else showToast('Trình duyệt chưa hỗ trợ biểu mẫu báo lỗi này.');
    });
    get('mayhen_error_404_report_close').addEventListener('click', closeDialog);
    get('mayhen_error_404_report_cancel').addEventListener('click', closeDialog);
    dialog.addEventListener('click', (event) => { if (event.target === dialog) closeDialog(); });

    async function copyText(text) {
      if (navigator.clipboard && window.isSecureContext) {
        try { await navigator.clipboard.writeText(text); return true; } catch (_) { /* Try fallback */ }
      }
      const field = document.createElement('textarea');
      field.value = text;
      field.setAttribute('readonly', '');
      field.style.cssText = 'position:fixed;left:-9999px;top:0;opacity:0';
      document.body.appendChild(field);
      field.select();
      let copied = false;
      try { copied = document.execCommand('copy'); } catch (_) { /* Clipboard unavailable */ }
      field.remove();
      return copied;
    }
    get('mayhen_error_404_report_form').addEventListener('submit', async (event) => {
      event.preventDefault();
      const reason = get('mayhen_error_404_report_reason').value;
      const note = get('mayhen_error_404_report_note').value.trim();
      const report = `BÁO LIÊN KẾT LỖI – ${MAYHEN_ERROR_404_CONFIG.brandNameUpper}\n\nĐường dẫn: ${window.location.href}\nVấn đề: ${reason}\nMô tả: ${note || 'Không có'}\nThời gian: ${new Date().toLocaleString('vi-VN')}`;
      const email = MAYHEN_ERROR_404_CONFIG.supportEmail.trim();
      if (email) {
        const subject = encodeURIComponent(`Báo liên kết lỗi – ${MAYHEN_ERROR_404_CONFIG.brandName}`);
        const body = encodeURIComponent(report);
        closeDialog();
        window.location.href = `mailto:${email}?subject=${subject}&body=${body}`;
        showToast('Đã mở ứng dụng email để bạn kiểm tra và gửi báo lỗi.');
        return;
      }
      const copied = await copyText(report);
      if (copied) { closeDialog(); showToast('Đã sao chép nội dung báo lỗi. Bạn có thể gửi cho bộ phận hỗ trợ.'); }
      else { showToast('Chưa thể sao chép tự động. Hãy thử trên HTTPS hoặc dùng trình duyệt khác.'); }
    });
  })();
</script>
</body>
</html>
