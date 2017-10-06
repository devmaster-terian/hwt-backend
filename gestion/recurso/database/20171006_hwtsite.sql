CREATE TABLE hwt_cliente
(
  rowid               INT AUTO_INCREMENT
    PRIMARY KEY,
  codigo_cliente      INT          NULL,
  nombre_corto        VARCHAR(16)  NULL,
  razon_social        VARCHAR(128) NULL,
  rfc                 VARCHAR(24)  NULL,
  dir_calle           VARCHAR(128) NULL,
  dir_num_interior    VARCHAR(12)  NULL,
  dir_num_exterior    VARCHAR(12)  NULL,
  dir_colonia         VARCHAR(64)  NULL,
  dir_municipio       VARCHAR(64)  NULL,
  dir_estado          VARCHAR(64)  NULL,
  dir_pais            VARCHAR(64)  NULL,
  codigo_postal       VARCHAR(10)  NULL,
  representante_legal VARCHAR(128) NULL,
  contacto_nombre     VARCHAR(128) NULL,
  contacto_cargo      VARCHAR(128) NULL,
  contacto_telefono   VARCHAR(32)  NULL,
  contacto_movil      VARCHAR(32)  NULL,
  contacto_email      VARCHAR(128) NULL,
  facturacion_email   VARCHAR(128) NULL,
  estado_cliente      VARCHAR(16)  NULL,
  CONSTRAINT hwt_cliente_rowid_uindex
  UNIQUE (rowid),
  CONSTRAINT hwt_cliente_codigo_cliente_uindex
  UNIQUE (codigo_cliente),
  CONSTRAINT hwt_cliente_nombre_corto_uindex
  UNIQUE (nombre_corto)
)
  COMMENT 'Tabla de Clientes';

CREATE INDEX hwt_cliente_dir_pais_dir_estado_index
  ON hwt_cliente (dir_pais, dir_estado);

CREATE INDEX hwt_cliente_facturacion_email_index
  ON hwt_cliente (facturacion_email);

CREATE INDEX hwt_cliente_razon_social_index
  ON hwt_cliente (razon_social);

CREATE INDEX hwt_cliente_rfc_index
  ON hwt_cliente (rfc);

CREATE TABLE hwt_consecionario
(
  rowid                INT AUTO_INCREMENT
    PRIMARY KEY,
  codigo_consecionario VARCHAR(32)  NOT NULL,
  descripcion          VARCHAR(255) NOT NULL,
  CONSTRAINT hwt_consecionario_sucursal_rowid_uindex
  UNIQUE (rowid)
)
  COMMENT 'Tabla de Consecionarios';

CREATE INDEX hwt_consecionario_sucursal_codigo_consecionario_index
  ON hwt_consecionario (codigo_consecionario);

CREATE TABLE hwt_consecionario_sucursal
(
  rowid                INT AUTO_INCREMENT
    PRIMARY KEY,
  codigo_consecionario VARCHAR(32)  NOT NULL,
  codigo_sucursal      VARCHAR(32)  NOT NULL,
  descripcion          VARCHAR(255) NULL,
  CONSTRAINT hwt_consecionario_sucursal_rowid_uindex
  UNIQUE (rowid)
)
  COMMENT 'Tabla de Sucursales de Consecionarios';

CREATE TABLE hwt_cotizacion
(
  rowid            INT AUTO_INCREMENT
    PRIMARY KEY,
  codigo_empresa   VARCHAR(16)                     NOT NULL,
  num_cotizacion   INT                             NOT NULL,
  codigo_cliente   INT                             NULL,
  fecha_cotizacion DATE                            NULL,
  usuario          VARCHAR(128)                    NULL,
  valor_subtotal   DECIMAL(10, 2)                  NULL,
  valor_impuesto   DECIMAL(10, 2)                  NULL,
  valor_total      DECIMAL(10, 2)                  NULL,
  estado           VARCHAR(16) DEFAULT 'PENDIENTE' NULL,
  CONSTRAINT hwt_cotizacion_rowid_uindex
  UNIQUE (rowid),
  CONSTRAINT hwt_cotizacion_codigo_empresa_num_cotizacion_uindex
  UNIQUE (codigo_empresa, num_cotizacion)
)
  COMMENT 'Cotización de Unidades';

CREATE INDEX hwt_cotizacion_codigo_cliente_index
  ON hwt_cotizacion (codigo_cliente);

CREATE INDEX hwt_cotizacion_usuario_index
  ON hwt_cotizacion (usuario);

CREATE TABLE hwt_cotizacion_unidad
(
  rowid           INT AUTO_INCREMENT
    PRIMARY KEY,
  codigo_empresa  VARCHAR(16) NOT NULL,
  num_cotizacion  INT         NOT NULL,
  num_partida     INT         NULL,
  codigo          VARCHAR(16) NOT NULL,
  precio_unitario FLOAT       NOT NULL,
  CONSTRAINT hwt_cotizacion_unidad_rowid_uindex
  UNIQUE (rowid),
  CONSTRAINT hwt_cotizacion_unidad_empresa_cotizacion_partida_uindex
  UNIQUE (codigo_empresa, num_cotizacion, num_partida)
)
  COMMENT 'Unidad perteneciente a Cotización';

CREATE INDEX hwt_cotizacion_unidad_codigo_index
  ON hwt_cotizacion_unidad (codigo);

CREATE INDEX hwt_cotizacion_unidad_num_cotizacion_index
  ON hwt_cotizacion_unidad (num_cotizacion);

CREATE TABLE hwt_imagen
(
  rowid         INT AUTO_INCREMENT
    PRIMARY KEY,
  origen        VARCHAR(32)  NOT NULL,
  documento     VARCHAR(64)  NOT NULL,
  identificador VARCHAR(64)  NOT NULL,
  numero        INT          NOT NULL,
  url           VARCHAR(256) NULL,
  estado        VARCHAR(32)  NULL,
  CONSTRAINT hwt_imagen_rowid_uindex
  UNIQUE (rowid),
  CONSTRAINT hwt_imagen_origen_identificador_numero_uindex
  UNIQUE (origen, identificador, numero)
)
  COMMENT 'HWT Imagen';

CREATE INDEX hwt_imagen_documento_index
  ON hwt_imagen (documento);

CREATE INDEX hwt_imagen_estado_index
  ON hwt_imagen (estado);

CREATE INDEX hwt_imagen_identificador_index
  ON hwt_imagen (identificador);

CREATE INDEX hwt_imagen_origen_documento_identificador_estado_index
  ON hwt_imagen (origen, documento, identificador, estado);

CREATE INDEX hwt_imagen_origen_index
  ON hwt_imagen (origen);

CREATE TABLE hwt_oportunidad_venta
(
  rowid                   INT AUTO_INCREMENT
    PRIMARY KEY,
  num_oportunidad         INT             NULL,
  situacion_oportunidad   VARCHAR(16)     NULL,
  codigo_gerente_regional VARCHAR(32)     NOT NULL,
  codigo_vendedor         VARCHAR(32)     NOT NULL,
  visita_fecha            DATE            NOT NULL,
  visita_ann              INT             NULL,
  visita_semana           INT             NULL,
  tipo_solicitante        VARCHAR(32)     NULL,
  tipo_empresa            VARCHAR(64)     NULL,
  codigo_cliente          INT             NULL,
  razon_social            VARCHAR(128)    NULL,
  contacto_nombre         VARCHAR(128)    NULL,
  contacto_cargo          VARCHAR(128)    NULL,
  contacto_telefono       VARCHAR(32)     NULL,
  contacto_movil          VARCHAR(32)     NULL,
  contacto_email          VARCHAR(128)    NULL,
  codigo_consecionario    VARCHAR(32)     NULL,
  solicitud_pais          VARCHAR(32)     NULL,
  solicitud_estado        VARCHAR(64)     NULL,
  solicitud_municipio     VARCHAR(128)    NULL,
  solicitud_ciudad        VARCHAR(128)    NULL,
  solicitud_cp            VARCHAR(16)     NULL,
  cantidad_solicitada     INT DEFAULT '0' NULL,
  marca                   VARCHAR(64)     NULL,
  modelo                  VARCHAR(64)     NULL,
  observaciones           VARCHAR(256)    NULL,
  cantidad_atendida       INT DEFAULT '0' NULL,
  cantidad_saldo          INT DEFAULT '0' NULL,
  CONSTRAINT hwt_necesidad_unidad_rowid_uindex
  UNIQUE (rowid),
  CONSTRAINT hwt_oportunidad_venta_num_oportunidad_uindex
  UNIQUE (num_oportunidad)
)
  COMMENT 'Necesidad de Unidad';

CREATE INDEX hwt_necesidad_gerente_regional_vendedor_index
  ON hwt_oportunidad_venta (codigo_gerente_regional, codigo_vendedor);

CREATE INDEX hwt_necesidad_unidad_codigo_consecionario_index
  ON hwt_oportunidad_venta (codigo_consecionario);

CREATE INDEX hwt_necesidad_unidad_codigo_gerente_regional_index
  ON hwt_oportunidad_venta (codigo_gerente_regional);

CREATE INDEX hwt_necesidad_unidad_codigo_vendedor_index
  ON hwt_oportunidad_venta (codigo_vendedor);

CREATE INDEX hwt_necesidad_unidad_contacto_movil_index
  ON hwt_oportunidad_venta (contacto_movil);

CREATE INDEX hwt_necesidad_unidad_contacto_nombre_index
  ON hwt_oportunidad_venta (contacto_nombre);

CREATE INDEX hwt_necesidad_unidad_contacto_telefono_index
  ON hwt_oportunidad_venta (contacto_telefono);

CREATE INDEX hwt_necesidad_unidad_marca_index
  ON hwt_oportunidad_venta (marca);

CREATE INDEX hwt_necesidad_unidad_modelo_index
  ON hwt_oportunidad_venta (modelo);

CREATE INDEX hwt_necesidad_unidad_solicitud_ciudad_index
  ON hwt_oportunidad_venta (solicitud_ciudad);

CREATE INDEX hwt_necesidad_unidad_solicitud_estado_index
  ON hwt_oportunidad_venta (solicitud_estado);

CREATE INDEX hwt_necesidad_unidad_tipo_empresa_index
  ON hwt_oportunidad_venta (tipo_empresa);

CREATE INDEX hwt_necesidad_unidad_tipo_solicitante_index
  ON hwt_oportunidad_venta (tipo_solicitante);

CREATE INDEX hwt_necesidad_unidad_visita_fecha_index
  ON hwt_oportunidad_venta (visita_fecha);

CREATE INDEX hwt_necesidad_unidad_visita_semana_index
  ON hwt_oportunidad_venta (visita_semana);

CREATE INDEX hwt_oportunidad_venta_codigo_cliente_index
  ON hwt_oportunidad_venta (codigo_cliente);

CREATE INDEX hwt_oportunidad_venta_num_oportunidad_index
  ON hwt_oportunidad_venta (num_oportunidad);

CREATE INDEX hwt_oportunidad_venta_razon_social_index
  ON hwt_oportunidad_venta (razon_social);

CREATE INDEX hwt_oportunidad_venta_situacion_oportunidad_index
  ON hwt_oportunidad_venta (situacion_oportunidad);

CREATE INDEX hwt_oportunidad_venta_visita_ann_visita_semana_index
  ON hwt_oportunidad_venta (visita_ann, visita_semana);

CREATE TABLE hwt_oportunidad_venta_cotizacion
(
  rowid                 INT AUTO_INCREMENT
    PRIMARY KEY,
  num_oportunidad_venta INT NULL,
  num_cotizacion        INT NULL,
  num_partida           INT NULL,
  CONSTRAINT hwt_oportunidad_venta_cotizacion_rowid_uindex
  UNIQUE (rowid)
)
  COMMENT 'Relación de Oportunidad de Venta con Cotizacion';

CREATE INDEX hwt_oportunidad_cotizacion_partida_index
  ON hwt_oportunidad_venta_cotizacion (num_cotizacion, num_partida);

CREATE INDEX hwt_oportunidad_venta_cotizacion_num_cotizacion_index
  ON hwt_oportunidad_venta_cotizacion (num_cotizacion);

CREATE INDEX hwt_oportunidad_venta_cotizacion_num_oportunidad_venta_index
  ON hwt_oportunidad_venta_cotizacion (num_oportunidad_venta);

CREATE TABLE hwt_pedido_venta
(
  rowid                        INT AUTO_INCREMENT
    PRIMARY KEY,
  num_pedido                   INT                             NOT NULL,
  situacion_pedido             VARCHAR(32) DEFAULT 'PENDIENTE' NOT NULL,
  fecha_pedido                 DATE                            NOT NULL,
  codigo_gerente_regional      VARCHAR(32)                     NULL,
  codigo_vendedor              VARCHAR(32)                     NULL,
  codigo_consecionario         VARCHAR(32)                     NULL,
  codigo_sucursal              VARCHAR(32)                     NULL,
  codigo_cliente               INT                             NOT NULL,
  cantidad_unidades            INT                             NULL,
  valor_con_cargo_cliente      DECIMAL(10, 2)                  NULL,
  valor_sin_cargo_cliente      DECIMAL(10, 2)                  NULL,
  valor_subtotal_unidades      DECIMAL(10, 2)                  NULL,
  valor_subtotal_adicionales   DECIMAL(10, 2)                  NULL,
  valor_subtotal               DECIMAL(10, 2)                  NOT NULL,
  valor_impuesto               DECIMAL(10, 2)                  NULL,
  valor_total                  DECIMAL(10, 2)                  NOT NULL,
  tipo_entrega                 VARCHAR(32)                     NULL,
  codigo_consecionario_entrega VARCHAR(32)                     NULL,
  codigo_sucursal_entrega      VARCHAR(32)                     NULL,
  entrega_observaciones        VARCHAR(255)                    NULL,
  integracion_num_pedido_erp   VARCHAR(32)                     NULL,
  integracion_factura_fecha    DATE                            NULL,
  integracion_factura_serie    VARCHAR(16)                     NULL,
  integracion_factura_folio    VARCHAR(16)                     NULL,
  usuario_implantacion         VARCHAR(32)                     NULL,
  fecha_implantacion           DATE                            NULL,
  usuario_actualizacion        VARCHAR(32)                     NULL,
  fecha_actualizacion          DATE                            NULL,
  usuario_cancelacion          VARCHAR(32)                     NULL,
  fecha_cancelacion            DATE                            NULL,
  CONSTRAINT hwt_pedido_venta_rowid_uindex
  UNIQUE (rowid),
  CONSTRAINT hwt_pedido_venta_num_pedido_uindex
  UNIQUE (num_pedido)
)
  COMMENT 'Tabla de Pedidos de Venta';

CREATE INDEX hwt_pedido_venta_codigo_cliente_index
  ON hwt_pedido_venta (codigo_cliente);

CREATE INDEX hwt_pedido_venta_codigo_consecionario_index
  ON hwt_pedido_venta (codigo_consecionario);

CREATE INDEX hwt_pedido_venta_codigo_gerente_regional_index
  ON hwt_pedido_venta (codigo_gerente_regional);

CREATE INDEX hwt_pedido_venta_codigo_vendedor_index
  ON hwt_pedido_venta (codigo_vendedor);

CREATE INDEX hwt_pedido_venta_fecha_pedido_index
  ON hwt_pedido_venta (fecha_pedido);

CREATE INDEX hwt_pedido_venta_situacion_pedido_index
  ON hwt_pedido_venta (situacion_pedido);

CREATE INDEX hwt_pedido_venta_tipo_entrega_index
  ON hwt_pedido_venta (tipo_entrega);

CREATE INDEX hwt_pedido_venta_usuario_actualizacion_fecha_actualizacion_index
  ON hwt_pedido_venta (usuario_actualizacion, fecha_actualizacion);

CREATE INDEX hwt_pedido_venta_usuario_cancelacion_fecha_cancelacion_index
  ON hwt_pedido_venta (usuario_cancelacion, fecha_cancelacion);

CREATE INDEX hwt_pedido_venta_usuario_implantacion_fecha_implantacion_index
  ON hwt_pedido_venta (usuario_implantacion, fecha_implantacion);

CREATE TABLE hwt_pedido_venta_adicional
(
  rowid                      INT AUTO_INCREMENT
    PRIMARY KEY,
  num_pedido                 INT            NULL,
  num_secuencia              INT            NULL,
  codigo_proveedor           VARCHAR(32)    NULL,
  descripcion                VARCHAR(255)   NULL,
  servicio_con_cargo_cliente DECIMAL(10, 2) NULL,
  servicio_sin_cargo_cliente DECIMAL(10, 2) NULL,
  CONSTRAINT hwt_pedido_venta_adicional_rowid_uindex
  UNIQUE (rowid)
)
  COMMENT 'Tabla de Servicios Adicionales al Pedido de Venta';

CREATE INDEX hwt_pedido_venta_adicional_codigo_proveedor_index
  ON hwt_pedido_venta_adicional (codigo_proveedor);

CREATE INDEX hwt_pedido_venta_adicional_num_pedido_num_sequencia_index
  ON hwt_pedido_venta_adicional (num_pedido, num_secuencia);

CREATE TABLE hwt_pedido_venta_linea
(
  rowid           INT AUTO_INCREMENT,
  num_pedido      INT            NOT NULL,
  num_partida     INT            NOT NULL,
  codigo          VARCHAR(32)    NULL,
  cantidad        INT            NULL,
  valor_unitario  DECIMAL(10, 2) NULL,
  valor_impuestos DECIMAL(10, 2) NULL,
  valor_partida   DECIMAL(10, 2) NULL,
  entrega_fecha   DATE           NULL,
  entrega_usuario VARCHAR(16)    NULL,
  CONSTRAINT hwt_pedido_venta_linea_rowid_uindex
  UNIQUE (rowid),
  CONSTRAINT hwt_pedido_venta_linea_num_pedido_num_partida_codigo_pk
  UNIQUE (num_pedido, num_partida, codigo)
)
  COMMENT 'Tabla de Líneas del Pedido de Venta';

CREATE INDEX hwt_pedido_venta_linea_codigo_index
  ON hwt_pedido_venta_linea (codigo);

CREATE INDEX hwt_pedido_venta_linea_entrega_fecha_index
  ON hwt_pedido_venta_linea (entrega_fecha);

CREATE INDEX hwt_pedido_venta_linea_entrega_usuario_index
  ON hwt_pedido_venta_linea (entrega_usuario);

CREATE TABLE hwt_propuesta_adquisicion
(
  rowid                INT AUTO_INCREMENT
    PRIMARY KEY,
  num_propuesta        INT          NOT NULL,
  fecha_propuesta      DATE         NULL,
  fecha_entrega        DATE         NULL,
  solicitante          VARCHAR(128) NULL,
  solicitante_email    VARCHAR(128) NULL,
  codigo_cliente       INT          NULL,
  razon_social         VARCHAR(128) NULL,
  contacto_nombre      VARCHAR(128) NULL,
  contacto_cargo       VARCHAR(128) NULL,
  contacto_telefono    VARCHAR(32)  NULL,
  contacto_movil       VARCHAR(32)  NULL,
  contacto_email       VARCHAR(128) NULL,
  prospecto            VARCHAR(128) NULL,
  prospecto_telefono   VARCHAR(32)  NULL,
  prospecto_email      VARCHAR(128) NULL,
  prospecto_cargo      VARCHAR(128) NULL,
  cant_unidades_nuevas INT          NULL,
  cant_unidades_usadas INT          NULL,
  observaciones        VARCHAR(256) NULL,
  situacion_propuesta  VARCHAR(32)  NULL,
  CONSTRAINT hwt_propuesta_adquisicion_rowid_uindex
  UNIQUE (rowid),
  CONSTRAINT hwt_propuesta_adquisicion_num_propuesta_uindex
  UNIQUE (num_propuesta)
)
  COMMENT 'Propuesta de Adquisición de Unidades';

CREATE INDEX hwt_propuesta_adquisicion_codigo_cliente_index
  ON hwt_propuesta_adquisicion (codigo_cliente);

CREATE INDEX hwt_propuesta_adquisicion_fecha_entrega_index
  ON hwt_propuesta_adquisicion (fecha_entrega);

CREATE INDEX hwt_propuesta_adquisicion_fecha_propuesta_index
  ON hwt_propuesta_adquisicion (fecha_propuesta);

CREATE INDEX hwt_propuesta_adquisicion_razon_social_index
  ON hwt_propuesta_adquisicion (razon_social);

CREATE INDEX hwt_propuesta_adquisicion_solicitante_index
  ON hwt_propuesta_adquisicion (solicitante);

CREATE INDEX hwt_propuesta_adquisicion_situacion_oportunidad_index
  ON hwt_propuesta_adquisicion (situacion_propuesta);

CREATE TABLE hwt_propuesta_adquisicion_unidad
(
  rowid                 INT AUTO_INCREMENT
    PRIMARY KEY,
  num_propuesta         INT          NULL,
  vin                   VARCHAR(24)  NULL,
  estado_unidad         VARCHAR(64)  NULL,
  marca                 VARCHAR(16)  NULL,
  modelo                VARCHAR(16)  NULL,
  ann_unidad            VARCHAR(4)   NULL,
  tipo_unidad           VARCHAR(64)  NULL,
  motor                 VARCHAR(64)  NULL,
  tipo_transmision      VARCHAR(64)  NULL,
  marca_transmision     VARCHAR(64)  NULL,
  capacidad_eje_trasero VARCHAR(64)  NULL,
  relacion_diferencial  VARCHAR(32)  NULL,
  distancia_ejes        VARCHAR(32)  NULL,
  kilometraje           VARCHAR(32)  NULL,
  cabina                VARCHAR(128) NULL,
  fecha_entrega         DATE         NULL,
  CONSTRAINT hwt_propuesta_adquisicion_unidad_rowid_uindex
  UNIQUE (rowid),
  CONSTRAINT hwt_propuesta_adquisicion_unidad_num_propuesta_vin_uindex
  UNIQUE (num_propuesta, vin)
)
  COMMENT 'Unidad de la Propuesta de Adquisición';

CREATE INDEX hwt_propuesta_adquisicion_unidad_ann_unidad_index
  ON hwt_propuesta_adquisicion_unidad (ann_unidad);

CREATE INDEX hwt_propuesta_adquisicion_unidad_fecha_entrega_index
  ON hwt_propuesta_adquisicion_unidad (fecha_entrega);

CREATE INDEX hwt_propuesta_adquisicion_unidad_marca_index
  ON hwt_propuesta_adquisicion_unidad (marca);

CREATE INDEX hwt_propuesta_adquisicion_unidad_modelo_index
  ON hwt_propuesta_adquisicion_unidad (modelo);

CREATE TABLE hwt_proveedor
(
  rowid               INT AUTO_INCREMENT
    PRIMARY KEY,
  codigo_proveedor    INT          NULL,
  nombre_corto        VARCHAR(16)  NULL,
  razon_social        VARCHAR(128) NULL,
  rfc                 VARCHAR(24)  NULL,
  dir_calle           VARCHAR(128) NULL,
  dir_num_interior    VARCHAR(12)  NULL,
  dir_num_exterior    VARCHAR(12)  NULL,
  dir_colonia         VARCHAR(64)  NULL,
  dir_municipio       VARCHAR(64)  NULL,
  dir_estado          VARCHAR(64)  NULL,
  dir_pais            VARCHAR(64)  NULL,
  codigo_postal       VARCHAR(10)  NULL,
  representante_legal VARCHAR(128) NULL,
  contacto_nombre     VARCHAR(128) NULL,
  contacto_telefono   VARCHAR(32)  NULL,
  contacto_movil      VARCHAR(32)  NULL,
  contacto_email      VARCHAR(128) NULL,
  facturacion_email   VARCHAR(128) NULL,
  estado_proveedor    VARCHAR(16)  NULL,
  CONSTRAINT hwt_proveedor_rowid_uindex
  UNIQUE (rowid),
  CONSTRAINT hwt_proveedor_codigo_proveedor_uindex
  UNIQUE (codigo_proveedor),
  CONSTRAINT hwt_proveedor_nombre_corto_uindex
  UNIQUE (nombre_corto)
)
  COMMENT 'Tabla de proveedors';

CREATE INDEX hwt_proveedor_dir_pais_dir_estado_index
  ON hwt_proveedor (dir_pais, dir_estado);

CREATE INDEX hwt_proveedor_facturacion_email_index
  ON hwt_proveedor (facturacion_email);

CREATE INDEX hwt_proveedor_razon_social_index
  ON hwt_proveedor (razon_social);

CREATE INDEX hwt_proveedor_rfc_index
  ON hwt_proveedor (rfc);

CREATE TABLE hwt_remolque
(
  rowid                               INT AUTO_INCREMENT
    PRIMARY KEY,
  vin                                 VARCHAR(45)  NOT NULL,
  volquete_incluido                   VARCHAR(8)   NULL,
  volquete_marca                      VARCHAR(64)  NULL,
  volquete_ann                        VARCHAR(12)  NULL,
  volquete_composicion                VARCHAR(64)  NULL,
  volquete_largo                      VARCHAR(64)  NULL,
  volquete_capacidad                  VARCHAR(64)  NULL,
  volquete_alto                       VARCHAR(64)  NULL,
  volquete_alto_paneles_laterales     VARCHAR(64)  NULL,
  volquete_forma_alza_balde           VARCHAR(64)  NULL,
  volquete_cubierta_cabina            VARCHAR(64)  NULL,
  volquete_sistema_carpa              VARCHAR(64)  NULL,
  volquete_estructura_inferior        VARCHAR(64)  NULL,
  volquete_num_paneles_puerta         VARCHAR(64)  NULL,
  volquete_rampa_regado               VARCHAR(64)  NULL,
  volquete_puerta                     VARCHAR(64)  NULL,
  volquete_calefaccion_balde          VARCHAR(32)  NULL,
  volquete_pin_cuerno_remolque        VARCHAR(32)  NULL,
  volquete_conexion_electricidad_aire VARCHAR(32)  NULL,
  cajon_incluido                      VARCHAR(8)   NULL,
  cajon_modelo                        VARCHAR(64)  NULL,
  cajon_ann                           VARCHAR(32)  NULL,
  cajon_peso_maximo                   VARCHAR(32)  NULL,
  cajon_tipo                          VARCHAR(32)  NULL,
  cajon_construccion                  VARCHAR(32)  NULL,
  cajon_largo                         VARCHAR(32)  NULL,
  cajon_ancho                         VARCHAR(32)  NULL,
  cajon_alto                          VARCHAR(32)  NULL,
  cajon_puerta_posterior              VARCHAR(32)  NULL,
  cajon_lado_derecho                  VARCHAR(32)  NULL,
  cajon_lado_izquierdo                VARCHAR(32)  NULL,
  cajon_tipo_piso                     VARCHAR(32)  NULL,
  cajon_sistema_carga                 VARCHAR(32)  NULL,
  cajon_sistema_carga_capacidad       VARCHAR(32)  NULL,
  cajon_logistica                     VARCHAR(32)  NULL,
  cajon_insulacion                    VARCHAR(32)  NULL,
  cajon_placa_antirresbalante         VARCHAR(32)  NULL,
  cajon_paredes_cubierta              VARCHAR(32)  NULL,
  cajon_techo_traslucido              VARCHAR(32)  NULL,
  cajon_capacidad_montacarga          VARCHAR(32)  NULL,
  cajon_sistema_descanso              VARCHAR(32)  NULL,
  cajon_guardachoque_posterior        VARCHAR(32)  NULL,
  cajon_dormitorio                    VARCHAR(32)  NULL,
  refrigeracion_incluido              VARCHAR(8)   NULL,
  refrigeracion_sistema_electrico     VARCHAR(32)  NULL,
  refrigeracion_marca                 VARCHAR(64)  NULL,
  refrigeracion_modelo                VARCHAR(32)  NULL,
  refrigeracion_horas                 VARCHAR(32)  NULL,
  refrigeracion_ann                   VARCHAR(32)  NULL,
  refrigeracion_tipo                  VARCHAR(32)  NULL,
  observaciones                       VARCHAR(256) NULL,
  CONSTRAINT hwt_remolque_rowid_uindex
  UNIQUE (rowid),
  CONSTRAINT hwt_remolque_vin_uindex
  UNIQUE (vin)
)
  COMMENT 'Remolque de la Unidad';

CREATE TABLE hwt_reporte_condicion
(
  rowid                 INT AUTO_INCREMENT
    PRIMARY KEY,
  num_reporte           INT         NOT NULL,
  vin                   VARCHAR(20) NOT NULL,
  fecha_reporte         DATE        NULL,
  usuario               VARCHAR(32) NULL,
  num_reparaciones      INT         NULL,
  precio_total_estimado FLOAT       NULL,
  CONSTRAINT hwt_reporte_condicion_rowid_uindex
  UNIQUE (rowid),
  CONSTRAINT hwt_reporte_condicion_num_reporte_uindex
  UNIQUE (num_reporte),
  CONSTRAINT hwt_reporte_condicion_vin_pk
  UNIQUE (vin)
)
  COMMENT 'Reporte de Condición';

CREATE INDEX hwt_reporte_condicion_fecha_reporte_pk
  ON hwt_reporte_condicion (fecha_reporte);

CREATE TABLE hwt_reporte_condicion_linea
(
  rowid                    INT AUTO_INCREMENT
    PRIMARY KEY,
  num_reporte              INT          NOT NULL,
  num_sequencia            INT          NOT NULL,
  cod_seccion              VARCHAR(16)  NOT NULL,
  cod_caracteristica       VARCHAR(256) NOT NULL,
  desc_caracteristica      VARCHAR(128) NULL,
  valor_referencia         VARCHAR(32)  NULL,
  estado                   VARCHAR(32)  NULL,
  observaciones            VARCHAR(256) NULL,
  fotografia               VARCHAR(256) NULL,
  precio_unitario_estimado FLOAT        NULL,
  CONSTRAINT hwt_reporte_condicion_linea_rowid_uindex
  UNIQUE (rowid),
  CONSTRAINT hwt_reporte_condicion_linea_reporte_sequencia_uindex
  UNIQUE (num_reporte, num_sequencia),
  CONSTRAINT hwt_reporte_condicion_linea_seccion_caracteristica_uindex
  UNIQUE (num_reporte, cod_seccion, cod_caracteristica)
)
  COMMENT 'Linea de Reporte de Condición';

CREATE INDEX hwt_reporte_condicion_linea_cod_caracteristica_index
  ON hwt_reporte_condicion_linea (cod_caracteristica);

CREATE INDEX hwt_reporte_condicion_linea_cod_seccion_index
  ON hwt_reporte_condicion_linea (cod_seccion);

CREATE INDEX hwt_reporte_condicion_linea_estado_index
  ON hwt_reporte_condicion_linea (estado);

CREATE TABLE hwt_vehiculo
(
  rowid                          INT AUTO_INCREMENT
    PRIMARY KEY,
  tipo_unidad                    VARCHAR(45)                     NULL,
  vin                            VARCHAR(45)                     NULL,
  codigo                         VARCHAR(16)                     NULL,
  ubicacion                      VARCHAR(45)                     NULL,
  modelo                         VARCHAR(45)                     NULL,
  marca                          VARCHAR(45)                     NULL,
  ann_unidad                     INT                             NULL,
  estado_unidad                  VARCHAR(45)                     NULL,
  color                          VARCHAR(45)                     NULL,
  motor                          VARCHAR(45)                     NULL,
  modelo_motor                   VARCHAR(45)                     NULL,
  potencia_motor                 INT                             NULL,
  numero_serie                   VARCHAR(45)                     NULL,
  marca_transmision              VARCHAR(45)                     NULL,
  modelo_transmision             VARCHAR(45)                     NULL,
  velocidades                    INT                             NULL,
  relacion_dif                   FLOAT                           NULL,
  kilometraje                    INT                             NULL,
  distancia_ejes                 INT                             NULL,
  tipo_cabina                    VARCHAR(45)                     NULL,
  propietario_anterior           VARCHAR(45)                     NULL,
  precio_sin_iva                 DECIMAL(10, 2)                  NULL,
  precio_con_iva                 DECIMAL(10, 2)                  NULL,
  fecha_publicacion              DATE                            NULL,
  fecha_venta                    DATE                            NULL
  COMMENT 'Tabla de Publicación de Vehículos Usados',
  traslado                       VARCHAR(45)                     NULL,
  suspension                     VARCHAR(64)                     NULL,
  odometro                       VARCHAR(64)                     NULL,
  hubometro                      VARCHAR(64)                     NULL,
  direccion_hidraulica           VARCHAR(64)                     NULL,
  aire_acondicionado             VARCHAR(64)                     NULL,
  sistema_hidraulico             VARCHAR(64)                     NULL,
  motor_cpl                      VARCHAR(64)                     NULL,
  motor_horas                    VARCHAR(64)                     NULL,
  motor_freno                    VARCHAR(64)                     NULL,
  tipo_transmision               VARCHAR(64)                     NULL,
  faldones_chasis                VARCHAR(32)                     NULL,
  copete_deflector               VARCHAR(32)                     NULL,
  extensiones_laterales          VARCHAR(32)                     NULL,
  defensa                        VARCHAR(64)                     NULL,
  vicera_exterior                VARCHAR(64)                     NULL,
  combustible_tipo               VARCHAR(64)                     NULL,
  combustible_tanques            VARCHAR(64)                     NULL,
  combustible_capacidad          VARCHAR(64)                     NULL,
  cabina_tipo_interior           VARCHAR(64)                     NULL,
  cabina_nivel_interior          VARCHAR(64)                     NULL,
  cabina_tipo_vestidura          VARCHAR(64)                     NULL,
  cabina_color_interior          VARCHAR(64)                     NULL,
  cabina_doble_cama              VARCHAR(32)                     NULL,
  cabina_dormitorio              VARCHAR(32)                     NULL,
  rines_delanteros               VARCHAR(32)                     NULL,
  llantas_delanteras_medidas     VARCHAR(32)                     NULL,
  eje_delantero_marca            VARCHAR(32)                     NULL,
  eje_delantero_capacidad        INT                             NULL,
  eje_delantero_posicion         VARCHAR(32)                     NULL,
  rines_traseros                 VARCHAR(32)                     NULL,
  llantas_traseras_medidas       VARCHAR(32)                     NULL,
  eje_trasero_marca              VARCHAR(32)                     NULL,
  eje_trasero_capacidad          INT                             NULL,
  eje_trasero_tipo               VARCHAR(64)                     NULL,
  eje_trasero_posicion           VARCHAR(32)                     NULL,
  tercer_eje                     VARCHAR(32)                     NULL,
  quinta_rueda                   VARCHAR(32)                     NULL,
  frenos_eje_direccion           VARCHAR(32)                     NULL,
  frenos_eje_traccion            VARCHAR(32)                     NULL,
  frenos_tapas_polvo_frente      VARCHAR(32)                     NULL,
  frenos_tapas_polvo_atras       VARCHAR(32)                     NULL,
  frenos_tercer_eje              VARCHAR(32)                     NULL,
  pintura_nueva                  VARCHAR(128)                    NULL,
  pintura_color                  VARCHAR(128)                    NULL,
  nombre_flota                   VARCHAR(128)                    NULL,
  contacto_nombre                VARCHAR(128)                    NULL,
  contacto_correo                VARCHAR(128)                    NULL,
  contacto_telefono              VARCHAR(128)                    NULL,
  contacto_extension             VARCHAR(128)                    NULL,
  direccion                      VARCHAR(128)                    NULL,
  ciudad                         VARCHAR(128)                    NULL,
  estado                         VARCHAR(128)                    NULL,
  pais                           VARCHAR(16)                     NULL,
  codigo_postal                  VARCHAR(128)                    NULL,
  estado_transmision             VARCHAR(32)                     NULL,
  cabina_suspension_asientos     VARCHAR(32)                     NULL,
  chasis                         VARCHAR(32)                     NULL,
  sistema_escape                 VARCHAR(32)                     NULL,
  toma_fuerza                    VARCHAR(32)                     NULL,
  cabina_tipo_asiento_operador   VARCHAR(64)                     NULL,
  cabina_tipo_asiento_copiloto   VARCHAR(64)                     NULL,
  sistema_hidraulico_componentes VARCHAR(256)                    NULL,
  serie_motor                    VARCHAR(64)                     NULL,
  tamano_unidad                  VARCHAR(16)                     NULL,
  set_delantero_llanta_izq       VARCHAR(16)                     NULL,
  set_delantero_llanta_der       VARCHAR(16)                     NULL,
  set_trasero1_llanta_izq_ext    VARCHAR(16)                     NULL,
  set_trasero1_llanta_izq_int    VARCHAR(16)                     NULL,
  set_trasero1_llanta_der_ext    VARCHAR(16)                     NULL,
  set_trasero1_llanta_der_int    VARCHAR(16)                     NULL,
  set_trasero2_llanta_izq_ext    VARCHAR(16)                     NULL,
  set_trasero2_llanta_izq_int    VARCHAR(16)                     NULL,
  set_trasero2_llanta_der_ext    VARCHAR(16)                     NULL,
  set_trasero2_llanta_der_int    VARCHAR(16)                     NULL,
  set_trasero3_llanta_izq_ext    VARCHAR(16)                     NULL,
  set_trasero3_llanta_izq_int    VARCHAR(16)                     NULL,
  set_trasero3_llanta_der_ext    VARCHAR(16)                     NULL,
  set_trasero3_llanta_der_int    VARCHAR(16)                     NULL,
  eje_delantero_izq              VARCHAR(16)                     NULL,
  eje_delantero_der              VARCHAR(16)                     NULL,
  eje_trasero1_izq               VARCHAR(16)                     NULL,
  eje_trasero1_der               VARCHAR(16)                     NULL,
  eje_trasero2_izq               VARCHAR(16)                     NULL,
  eje_trasero2_der               VARCHAR(16)                     NULL,
  eje_trasero3_izq               VARCHAR(16)                     NULL,
  eje_trasero3_der               VARCHAR(16)                     NULL,
  tanque1_material               VARCHAR(64)                     NULL,
  tanque1_capacidad              VARCHAR(64)                     NULL,
  tanque2_material               VARCHAR(64)                     NULL,
  tanque2_capacidad              VARCHAR(64)                     NULL,
  tanque3_material               VARCHAR(64)                     NULL,
  tanque3_capacidad              VARCHAR(64)                     NULL,
  radio_instalado                VARCHAR(128) DEFAULT 'NO RADIO' NULL,
  espejos                        VARCHAR(64)                     NULL,
  frenos                         VARCHAR(64)                     NULL,
  CONSTRAINT hwt_vehiculo_usado_rowid_uindex
  UNIQUE (rowid),
  CONSTRAINT hwt_vehiculo_usado_codigo_uindex
  UNIQUE (codigo),
  CONSTRAINT idxCodigoVehiculo
  UNIQUE (codigo)
);

CREATE INDEX idxAnn
  ON hwt_vehiculo (ann_unidad);

CREATE INDEX idxEstado
  ON hwt_vehiculo (estado_unidad);

CREATE INDEX idxMarca
  ON hwt_vehiculo (marca);

CREATE INDEX idxModelo
  ON hwt_vehiculo (modelo);

CREATE INDEX idxNumeroSerie
  ON hwt_vehiculo (numero_serie);

CREATE INDEX idxTipoUnidad
  ON hwt_vehiculo (tipo_unidad);

CREATE INDEX idxUbicacion
  ON hwt_vehiculo (ubicacion);

CREATE INDEX idxVin
  ON hwt_vehiculo (vin);

CREATE TABLE sys_empresa
(
  rowid                      INT AUTO_INCREMENT
    PRIMARY KEY,
  codigo_empresa             VARCHAR(16)  NOT NULL,
  nombre_empresa             VARCHAR(128) NULL,
  banco_moneda               VARCHAR(32)  NULL,
  banco_institucion          VARCHAR(64)  NULL,
  banco_razon_social         VARCHAR(64)  NULL,
  banco_cuenta               VARCHAR(64)  NULL,
  banco_cuenta_clabe         VARCHAR(64)  NULL,
  banco_cuenta_swift         VARCHAR(64)  NULL,
  banco_anotaciones          VARCHAR(256) NULL,
  fiscal_domicilio           VARCHAR(128) NULL,
  fiscal_identificacion      VARCHAR(64)  NULL,
  fiscal_razon_social        VARCHAR(64)  NULL,
  fiscal_representante_legal VARCHAR(128) NULL,
  CONSTRAINT hwt_empresa_rowid_uindex
  UNIQUE (rowid),
  CONSTRAINT hwt_empresa_codigo_empresa_uindex
  UNIQUE (codigo_empresa)
)
  COMMENT 'HWT Tabla de Empresa';

CREATE TABLE sys_localizacion
(
  rowid         INT AUTO_INCREMENT
    PRIMARY KEY,
  cod_pais      VARCHAR(16) DEFAULT 'MEX'    NOT NULL,
  pais          VARCHAR(64) DEFAULT 'México' NOT NULL,
  cod_estado    INT                          NOT NULL,
  estado        VARCHAR(35)                  NOT NULL,
  cod_municipio INT                          NOT NULL,
  municipio     VARCHAR(60)                  NOT NULL,
  ciudad        VARCHAR(60)                  NULL,
  zona          VARCHAR(15)                  NOT NULL,
  cp            INT                          NOT NULL,
  asentamiento  VARCHAR(70)                  NOT NULL,
  tipo          VARCHAR(64)                  NOT NULL
);

CREATE INDEX sys_estado_ciudad_ciudad_index
  ON sys_localizacion (ciudad);

CREATE INDEX sys_estado_ciudad_cod_estado_cod_municipio_index
  ON sys_localizacion (cod_estado, cod_municipio);

CREATE INDEX sys_estado_ciudad_cod_estado_index
  ON sys_localizacion (cod_estado);

CREATE INDEX sys_estado_ciudad_cod_municipio_index
  ON sys_localizacion (cod_municipio);

CREATE INDEX sys_estado_ciudad_cod_pais_index
  ON sys_localizacion (cod_pais);

CREATE INDEX sys_estado_ciudad_cp_index
  ON sys_localizacion (cp);

CREATE INDEX sys_estado_ciudad_estado_index
  ON sys_localizacion (estado);

CREATE INDEX sys_estado_ciudad_municipio_index
  ON sys_localizacion (municipio);

CREATE INDEX sys_estado_ciudad_pais_estado_index
  ON sys_localizacion (pais, estado);

CREATE INDEX sys_estado_ciudad_pais_estado_municipio_ciudad_index
  ON sys_localizacion (pais, estado, municipio, ciudad);

CREATE INDEX sys_estado_ciudad_pais_index
  ON sys_localizacion (pais);

CREATE INDEX sys_estado_ciudad_tipo_index
  ON sys_localizacion (tipo);

CREATE INDEX sys_estado_ciudad_zona_index
  ON sys_localizacion (zona);

CREATE TABLE sys_perfil
(
  rowid         INT AUTO_INCREMENT
    PRIMARY KEY,
  codigo_perfil VARCHAR(16)  NOT NULL,
  nombre_perfil VARCHAR(128) NOT NULL,
  CONSTRAINT hwt_usuario_perfil_rowid_uindex
  UNIQUE (rowid),
  CONSTRAINT hwt_usuario_perfil_codigo_perfil_uindex
  UNIQUE (codigo_perfil)
)
  COMMENT 'HWT Perfil del Usuario';

CREATE TABLE sys_sistema
(
  rowid           INT AUTO_INCREMENT
    PRIMARY KEY,
  codigo_sistema  VARCHAR(16)  NOT NULL,
  nombre_sistema  VARCHAR(128) NOT NULL,
  version_sistema VARCHAR(16)  NULL,
  url_logotipo    VARCHAR(256) NULL,
  CONSTRAINT hwt_sistema_rowid_uindex
  UNIQUE (rowid),
  CONSTRAINT hwt_sistema_codigo_sistema_uindex
  UNIQUE (codigo_sistema)
)
  COMMENT 'HWT Tabla de Sistemas';

CREATE TABLE sys_usuario
(
  rowid         INT AUTO_INCREMENT
    PRIMARY KEY,
  usuario       VARCHAR(128) NULL,
  acceso        VARCHAR(128) NOT NULL,
  nombre        VARCHAR(128) NOT NULL,
  email         VARCHAR(128) NULL,
  ultimo_acceso DATETIME     NULL,
  telefono      VARCHAR(64)  NULL,
  movil         VARCHAR(64)  NULL,
  CONSTRAINT hwt_usuario_rowid_uindex
  UNIQUE (rowid),
  CONSTRAINT hwt_usuario_usuario_uindex
  UNIQUE (usuario)
)
  COMMENT 'HWT Tabla de Usuarios';

CREATE TABLE sys_usuario_perfil
(
  rowid         INT AUTO_INCREMENT
    PRIMARY KEY,
  usuario       VARCHAR(128)    NOT NULL,
  codigo_perfil VARCHAR(16)     NOT NULL,
  principal     INT DEFAULT '0' NULL,
  CONSTRAINT sys_usuario_perfil_rowid_uindex
  UNIQUE (rowid),
  CONSTRAINT sys_usuario_perfil_usuario_codigo_perfil_uindex
  UNIQUE (usuario, codigo_perfil)
)
  COMMENT 'Relación de Usuarios con el Perfil';

CREATE INDEX sys_usuario_perfil_usuario_index
  ON sys_usuario_perfil (usuario);

CREATE TABLE work_config
(
  rowid       INT AUTO_INCREMENT
    PRIMARY KEY,
  code_config VARCHAR(64)  NOT NULL,
  description VARCHAR(128) NULL,
  CONSTRAINT work_config_rowid_uindex
  UNIQUE (rowid),
  CONSTRAINT work_config_code_config_uindex
  UNIQUE (code_config)
)
  COMMENT 'Configuracion';

CREATE TABLE work_config_param
(
  code_config VARCHAR(64)   NOT NULL,
  code_param  VARCHAR(64)   NOT NULL,
  description VARCHAR(128)  NULL,
  value       VARCHAR(2048) NULL,
  CONSTRAINT work_config_param_code_config_code_param_uindex
  UNIQUE (code_config, code_param)
)
  COMMENT 'Parámetro de Configuracion';

CREATE TABLE work_sequencer
(
  sequence_name      VARCHAR(100)                  NOT NULL
    PRIMARY KEY,
  sequence_increment INT DEFAULT '1'               NOT NULL,
  sequence_min_value INT DEFAULT '1'               NOT NULL,
  sequence_max_value BIGINT DEFAULT '999999999999' NOT NULL,
  sequence_cur_value BIGINT DEFAULT '1'            NULL,
  sequence_cycle     TINYINT(1) DEFAULT '0'        NOT NULL
);

CREATE FUNCTION nextval(seq_name VARCHAR(100))
  RETURNS BIGINT
  BEGIN
    DECLARE cur_val BIGINT(20);

    SELECT sequence_cur_value
    INTO cur_val
    FROM
      work_sequencer
    WHERE
      sequence_name = seq_name;

    IF cur_val IS NOT NULL
    THEN
      UPDATE
        work_sequencer
      SET
        sequence_cur_value = IF(
            (sequence_cur_value + sequence_increment) > sequence_max_value,
            IF(
                sequence_cycle = TRUE,
                sequence_min_value,
                NULL
            ),
            sequence_cur_value + sequence_increment
        )
      WHERE
        sequence_name = seq_name;
    END IF;

    RETURN cur_val;
  END;

