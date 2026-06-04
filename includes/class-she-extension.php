<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class SHE_Extension {

    private static $instance = null;

    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'elementor/element/section/section_layout/after_section_end',   [ $this, 'register_controls' ], 10, 2 );
        add_action( 'elementor/element/container/section_layout/after_section_end', [ $this, 'register_controls' ], 10, 2 );
        add_action( 'elementor/frontend/section/before_render',   [ $this, 'before_render' ] );
        add_action( 'elementor/frontend/container/before_render', [ $this, 'before_render' ] );
    }

    public function register_controls( $element ) {
        $element->start_controls_section( 'she_section', [
            'label' => '📌 Sticky Header',
            'tab'   => \Elementor\Controls_Manager::TAB_LAYOUT,
        ] );

        $element->add_control( 'she_enable', [
            'label' => 'Activar Sticky Header', 'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => 'Si', 'label_off' => 'No', 'return_value' => 'yes', 'default' => '',
        ] );
        $element->add_control( 'she_heading_behavior', [
            'label' => 'Comportamiento', 'type' => \Elementor\Controls_Manager::HEADING,
            'separator' => 'before', 'condition' => [ 'she_enable' => 'yes' ],
        ] );
        $element->add_control( 'she_mode', [
            'label' => 'Modo Sticky', 'type' => \Elementor\Controls_Manager::SELECT,
            'default' => 'always',
            'options' => [
                'always'       => 'Siempre visible (tras offset)',
                'scroll-up'    => 'Solo al hacer scroll hacia arriba',
                'after-offset' => 'Aparecer al pasar el offset',
            ],
            'condition' => [ 'she_enable' => 'yes' ],
        ] );
        $element->add_control( 'she_offset', [
            'label' => 'Offset de Activacion (px)', 'type' => \Elementor\Controls_Manager::NUMBER,
            'default' => 80, 'min' => 0, 'max' => 2000, 'step' => 10,
            'condition' => [ 'she_enable' => 'yes' ],
        ] );
        $element->add_control( 'she_hide_on_down', [
            'label' => 'Ocultar al Hacer Scroll Abajo', 'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => 'Si', 'label_off' => 'No', 'return_value' => 'yes', 'default' => '',
            'condition' => [ 'she_enable' => 'yes', 'she_mode' => 'always' ],
        ] );
        $element->add_control( 'she_heading_animation', [
            'label' => 'Animacion', 'type' => \Elementor\Controls_Manager::HEADING,
            'separator' => 'before', 'condition' => [ 'she_enable' => 'yes' ],
        ] );
        $element->add_control( 'she_enter_animation', [
            'label' => 'Animacion de Entrada', 'type' => \Elementor\Controls_Manager::SELECT,
            'default' => 'slide-down',
            'options' => [ 'none' => 'Ninguna', 'slide-down' => 'Slide Down', 'fade-in' => 'Fade In', 'zoom-in' => 'Zoom In' ],
            'condition' => [ 'she_enable' => 'yes' ],
        ] );
        $element->add_control( 'she_duration', [
            'label' => 'Duracion Transicion (ms)', 'type' => \Elementor\Controls_Manager::SLIDER,
            'range' => [ 'px' => [ 'min' => 100, 'max' => 1000, 'step' => 50 ] ],
            'default' => [ 'size' => 350 ], 'condition' => [ 'she_enable' => 'yes' ],
        ] );
        $element->add_control( 'she_easing', [
            'label' => 'Curva de Easing', 'type' => \Elementor\Controls_Manager::SELECT,
            'default' => 'ease',
            'options' => [ 'ease' => 'ease', 'ease-in-out' => 'ease-in-out', 'ease-out' => 'ease-out', 'linear' => 'linear', 'cubic-bezier' => 'Suave (spring)' ],
            'condition' => [ 'she_enable' => 'yes' ],
        ] );
        $element->add_control( 'she_heading_styles', [
            'label' => 'Estilos en Estado Sticky', 'type' => \Elementor\Controls_Manager::HEADING,
            'separator' => 'before', 'condition' => [ 'she_enable' => 'yes' ],
        ] );
        $element->add_control( 'she_bg_color', [
            'label' => 'Color de Fondo', 'type' => \Elementor\Controls_Manager::COLOR,
            'default' => '', 'condition' => [ 'she_enable' => 'yes' ],
        ] );
        $element->add_control( 'she_backdrop_blur', [
            'label' => 'Backdrop Blur (glassmorphism)', 'type' => \Elementor\Controls_Manager::SLIDER,
            'range' => [ 'px' => [ 'min' => 0, 'max' => 40 ] ],
            'default' => [ 'size' => 0 ], 'condition' => [ 'she_enable' => 'yes' ],
        ] );
        $element->add_control( 'she_shadow_enable', [
            'label' => 'Agregar Sombra', 'type' => \Elementor\Controls_Manager::SWITCHER,
            'label_on' => 'Si', 'label_off' => 'No', 'return_value' => 'yes', 'default' => 'yes',
            'condition' => [ 'she_enable' => 'yes' ],
        ] );
        $element->add_control( 'she_shadow_color', [
            'label' => 'Color de Sombra', 'type' => \Elementor\Controls_Manager::COLOR,
            'default' => 'rgba(0,0,0,0.12)',
            'condition' => [ 'she_enable' => 'yes', 'she_shadow_enable' => 'yes' ],
        ] );
        $element->add_control( 'she_shadow_intensity', [
            'label' => 'Intensidad de Sombra', 'type' => \Elementor\Controls_Manager::SLIDER,
            'range' => [ 'px' => [ 'min' => 1, 'max' => 60 ] ],
            'default' => [ 'size' => 20 ],
            'condition' => [ 'she_enable' => 'yes', 'she_shadow_enable' => 'yes' ],
        ] );
        $element->add_responsive_control( 'she_padding', [
            'label' => 'Padding en Estado Sticky', 'type' => \Elementor\Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em', '%' ], 'condition' => [ 'she_enable' => 'yes' ],
        ] );
        $element->add_control( 'she_border_bottom', [
            'label' => 'Borde Inferior al Activarse', 'type' => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes', 'default' => '', 'condition' => [ 'she_enable' => 'yes' ],
        ] );
        $element->add_control( 'she_border_color', [
            'label' => 'Color del Borde Inferior', 'type' => \Elementor\Controls_Manager::COLOR,
            'default' => 'rgba(0,0,0,0.08)',
            'condition' => [ 'she_enable' => 'yes', 'she_border_bottom' => 'yes' ],
        ] );
        $element->add_control( 'she_heading_advanced', [
            'label' => 'Avanzado', 'type' => \Elementor\Controls_Manager::HEADING,
            'separator' => 'before', 'condition' => [ 'she_enable' => 'yes' ],
        ] );
        $element->add_control( 'she_zindex', [
            'label' => 'Z-Index', 'type' => \Elementor\Controls_Manager::NUMBER,
            'default' => 9999, 'min' => 1, 'max' => 99999, 'condition' => [ 'she_enable' => 'yes' ],
        ] );
        $element->add_control( 'she_sticky_class', [
            'label' => 'Clase CSS Adicional (cuando sticky)', 'type' => \Elementor\Controls_Manager::TEXT,
            'placeholder' => 'header-is-sticky', 'condition' => [ 'she_enable' => 'yes' ],
        ] );
        $element->add_control( 'she_disable_mobile', [
            'label' => 'Desactivar en Movil', 'type' => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes', 'default' => '', 'condition' => [ 'she_enable' => 'yes' ],
        ] );
        $element->add_control( 'she_disable_tablet', [
            'label' => 'Desactivar en Tablet', 'type' => \Elementor\Controls_Manager::SWITCHER,
            'return_value' => 'yes', 'default' => '', 'condition' => [ 'she_enable' => 'yes' ],
        ] );

        $element->end_controls_section();
    }

    public function before_render( $element ) {
        $s = $element->get_settings_for_display();
        if ( empty( $s['she_enable'] ) || 'yes' !== $s['she_enable'] ) return;

        $easing_map = [
            'ease' => 'ease', 'ease-in-out' => 'ease-in-out', 'ease-out' => 'ease-out',
            'linear' => 'linear', 'cubic-bezier' => 'cubic-bezier(0.34,1.56,0.64,1)',
        ];

        $padding = null;
        $pad = $s['she_padding'] ?? [];
        if ( ! empty( $pad ) ) {
            $u = $pad['unit'] ?? 'px';
            $p = array_filter([
                'top'    => isset($pad['top'])    && $pad['top']    !== '' ? $pad['top'].$u    : null,
                'right'  => isset($pad['right'])  && $pad['right']  !== '' ? $pad['right'].$u  : null,
                'bottom' => isset($pad['bottom']) && $pad['bottom'] !== '' ? $pad['bottom'].$u : null,
                'left'   => isset($pad['left'])   && $pad['left']   !== '' ? $pad['left'].$u   : null,
            ]);
            if ( ! empty( $p ) ) $padding = $p;
        }

        $config = [
            'mode'          => $s['she_mode']           ?? 'always',
            'offset'        => (int)($s['she_offset'] ?? 80),
            'hideOnDown'    => ($s['she_hide_on_down'] ?? '') === 'yes',
            'animation'     => $s['she_enter_animation'] ?? 'slide-down',
            'duration'      => (int)($s['she_duration']['size'] ?? 350),
            'easing'        => $easing_map[$s['she_easing'] ?? 'ease'] ?? 'ease',
            'bgColor'       => $s['she_bg_color']        ?? '',
            'blur'          => (int)($s['she_backdrop_blur']['size'] ?? 0),
            'shadow'        => ($s['she_shadow_enable'] ?? '') === 'yes',
            'shadowColor'   => $s['she_shadow_color']    ?? 'rgba(0,0,0,0.12)',
            'shadowSize'    => (int)($s['she_shadow_intensity']['size'] ?? 20),
            'borderBottom'  => ($s['she_border_bottom'] ?? '') === 'yes',
            'borderColor'   => $s['she_border_color']    ?? 'rgba(0,0,0,0.08)',
            'zIndex'        => (int)($s['she_zindex'] ?? 9999),
            'stickyClass'   => sanitize_html_class($s['she_sticky_class'] ?? ''),
            'disableMobile' => ($s['she_disable_mobile'] ?? '') === 'yes',
            'disableTablet' => ($s['she_disable_tablet'] ?? '') === 'yes',
        ];
        if ( $padding ) $config['padding'] = $padding;

        $element->add_render_attribute( '_wrapper', [
            'data-she'        => '1',
            'data-she-config' => wp_json_encode( $config ),
        ] );
    }
}
