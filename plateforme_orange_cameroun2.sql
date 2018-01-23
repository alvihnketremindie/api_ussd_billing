--
-- PostgreSQL database dump
--

SET statement_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


SET search_path = public, pg_catalog;

--
-- Name: activation; Type: TYPE; Schema: public; Owner: digital
--

CREATE TYPE activation AS ENUM (
    'YES',
    'NO'
);


ALTER TYPE public.activation OWNER TO digital;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: smsqueues; Type: TABLE; Schema: public; Owner: digital; Tablespace: 
--

CREATE TABLE smsqueues (
    id integer NOT NULL,
    date_insertion timestamp without time zone NOT NULL,
    "senderName" character varying(11) NOT NULL,
    message text NOT NULL,
    receiver character varying(50) NOT NULL,
    date_process timestamp without time zone NOT NULL,
    date_envoi_sms timestamp without time zone NOT NULL,
    date_reception_dlr timestamp without time zone NOT NULL,
    message_md5 character varying(50) NOT NULL,
    statut character varying(30) DEFAULT 'pending'::character varying NOT NULL
);


ALTER TABLE public.smsqueues OWNER TO digital;

--
-- Name: smsqueues_id_seq; Type: SEQUENCE; Schema: public; Owner: digital
--

CREATE SEQUENCE smsqueues_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.smsqueues_id_seq OWNER TO digital;

--
-- Name: smsqueues_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: digital
--

ALTER SEQUENCE smsqueues_id_seq OWNED BY smsqueues.id;


--
-- Name: test_app; Type: TABLE; Schema: public; Owner: digital; Tablespace: 
--

CREATE TABLE test_app (
    id integer NOT NULL,
    servicecode character varying(20),
    telephone character varying(20),
    url character varying(200)
);


ALTER TABLE public.test_app OWNER TO digital;

--
-- Name: test_app_id_seq; Type: SEQUENCE; Schema: public; Owner: digital
--

CREATE SEQUENCE test_app_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.test_app_id_seq OWNER TO digital;

--
-- Name: test_app_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: digital
--

ALTER SEQUENCE test_app_id_seq OWNED BY test_app.id;


--
-- Name: ussd_sessions; Type: TABLE; Schema: public; Owner: digital; Tablespace: 
--

CREATE TABLE ussd_sessions (
    id integer NOT NULL,
    date timestamp without time zone,
    sessionid text,
    msisdn text,
    pagelevel text,
    previous text,
    next text,
    statut character varying(10) DEFAULT 'YES'::character varying
);


ALTER TABLE public.ussd_sessions OWNER TO digital;

--
-- Name: ussd_sessions_id_seq; Type: SEQUENCE; Schema: public; Owner: digital
--

CREATE SEQUENCE ussd_sessions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.ussd_sessions_id_seq OWNER TO digital;

--
-- Name: ussd_sessions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: digital
--

ALTER SEQUENCE ussd_sessions_id_seq OWNED BY ussd_sessions.id;


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: digital
--

ALTER TABLE ONLY smsqueues ALTER COLUMN id SET DEFAULT nextval('smsqueues_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: digital
--

ALTER TABLE ONLY test_app ALTER COLUMN id SET DEFAULT nextval('test_app_id_seq'::regclass);


--
-- Name: id; Type: DEFAULT; Schema: public; Owner: digital
--

ALTER TABLE ONLY ussd_sessions ALTER COLUMN id SET DEFAULT nextval('ussd_sessions_id_seq'::regclass);


--
-- Data for Name: smsqueues; Type: TABLE DATA; Schema: public; Owner: digital
--



--
-- Name: smsqueues_id_seq; Type: SEQUENCE SET; Schema: public; Owner: digital
--

SELECT pg_catalog.setval('smsqueues_id_seq', 1, false);


--
-- Data for Name: test_app; Type: TABLE DATA; Schema: public; Owner: digital
--



--
-- Name: test_app_id_seq; Type: SEQUENCE SET; Schema: public; Owner: digital
--

SELECT pg_catalog.setval('test_app_id_seq', 1, false);


--
-- Data for Name: ussd_sessions; Type: TABLE DATA; Schema: public; Owner: digital
--

INSERT INTO ussd_sessions VALUES (26, '2017-02-28 17:53:03', 'NO-SessionId', 'NO-MSISDN', '0_4_1', 'choix_rubrique_2_1', 'menu_non_abonne_2', 'NO');
INSERT INTO ussd_sessions VALUES (27, '2017-02-28 17:53:20', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_non_abonne_2', 'menu_groupe_0_1', 'YES');
INSERT INTO ussd_sessions VALUES (28, '2017-02-28 17:53:29', 'NO-SessionId', 'NO-MSISDN', '0_2', 'menu_groupe_0_1', 'menu_non_abonne_8', 'NO');
INSERT INTO ussd_sessions VALUES (29, '2017-02-28 17:53:40', 'NO-SessionId', 'NO-MSISDN', '0_2_2', 'menu_non_abonne_8', 'confirm_abonn_YES_8_factu_HEBDO', 'NO');
INSERT INTO ussd_sessions VALUES (30, '2017-02-28 17:53:50', 'NO-SessionId', 'NO-MSISDN', '0_2', 'menu_non_abonne_8', 'menu_groupe_0_1', 'NO');
INSERT INTO ussd_sessions VALUES (31, '2017-02-28 17:53:54', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_groupe_0_1', 'menu_groupe_0_1', 'YES');
INSERT INTO ussd_sessions VALUES (1, '2017-02-28 00:00:00', 'NO-SessionId', 'NO-MSISDN', '0', 'menu', 'menu_groupe_0_1', 'NO');
INSERT INTO ussd_sessions VALUES (2, '2017-02-28 17:39:02', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_groupe_0_1', 'menu_groupe_0_1', 'NO');
INSERT INTO ussd_sessions VALUES (3, '2017-02-28 17:40:12', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_groupe_0_1', 'menu_groupe_0_1', 'NO');
INSERT INTO ussd_sessions VALUES (4, '2017-02-28 17:40:22', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_groupe_0_1', 'menu_groupe_0_1', 'NO');
INSERT INTO ussd_sessions VALUES (5, '2017-02-28 17:40:30', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_groupe_0_1', 'menu_groupe_0_1', 'NO');
INSERT INTO ussd_sessions VALUES (6, '2017-02-28 17:40:31', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_groupe_0_1', 'menu_groupe_0_1', 'NO');
INSERT INTO ussd_sessions VALUES (7, '2017-02-28 17:42:12', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_groupe_0_1', 'menu_groupe_0_1', 'NO');
INSERT INTO ussd_sessions VALUES (8, '2017-02-28 17:42:34', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_groupe_0_1', 'menu_groupe_0_1', 'NO');
INSERT INTO ussd_sessions VALUES (9, '2017-02-28 17:42:42', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_groupe_0_1', 'menu_groupe_0_1', 'NO');
INSERT INTO ussd_sessions VALUES (10, '2017-02-28 17:42:43', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_groupe_0_1', 'menu_groupe_0_1', 'NO');
INSERT INTO ussd_sessions VALUES (11, '2017-02-28 17:42:48', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_groupe_0_1', 'menu_groupe_0_1', 'NO');
INSERT INTO ussd_sessions VALUES (12, '2017-02-28 17:42:49', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_groupe_0_1', 'menu_groupe_0_1', 'NO');
INSERT INTO ussd_sessions VALUES (13, '2017-02-28 17:43:44', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_groupe_0_1', 'menu_groupe_0_1', 'NO');
INSERT INTO ussd_sessions VALUES (14, '2017-02-28 17:44:32', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_groupe_0_1', 'menu_groupe_0_1', 'NO');
INSERT INTO ussd_sessions VALUES (15, '2017-02-28 17:48:20', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_groupe_0_1', 'menu_groupe_0_1', 'NO');
INSERT INTO ussd_sessions VALUES (16, '2017-02-28 17:49:11', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_groupe_0_1', 'menu_groupe_0_1', 'NO');
INSERT INTO ussd_sessions VALUES (17, '2017-02-28 17:50:10', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_groupe_0_1', 'menu_groupe_0_1', 'NO');
INSERT INTO ussd_sessions VALUES (18, '2017-02-28 17:51:15', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_groupe_0_1', 'menu_groupe_0_1', 'NO');
INSERT INTO ussd_sessions VALUES (19, '2017-02-28 17:52:16', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_groupe_0_1', 'menu_groupe_0_1', 'NO');
INSERT INTO ussd_sessions VALUES (20, '2017-02-28 17:52:29', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_groupe_0_1', 'menu_groupe_0_1', 'YES');
INSERT INTO ussd_sessions VALUES (21, '2017-02-28 17:52:31', 'NO-SessionId', 'NO-MSISDN', '0_5', 'menu_groupe_0_1', 'menu_non_abonne_10', 'NO');
INSERT INTO ussd_sessions VALUES (22, '2017-02-28 17:52:43', 'NO-SessionId', 'NO-MSISDN', '0', 'menu_non_abonne_10', 'menu_groupe_0_1', 'YES');
INSERT INTO ussd_sessions VALUES (23, '2017-02-28 17:52:49', 'NO-SessionId', 'NO-MSISDN', '0_4', 'menu_groupe_0_1', 'menu_non_abonne_2', 'YES');
INSERT INTO ussd_sessions VALUES (24, '2017-02-28 17:52:52', 'NO-SessionId', 'NO-MSISDN', '0_4_1', 'menu_non_abonne_2', 'choix_rubrique_2_1', 'NO');
INSERT INTO ussd_sessions VALUES (25, '2017-02-28 17:52:53', 'NO-SessionId', 'NO-MSISDN', '0_4_1_2', 'choix_rubrique_2_1', 'consultation_langue_2_2', 'NO');


--
-- Name: ussd_sessions_id_seq; Type: SEQUENCE SET; Schema: public; Owner: digital
--

SELECT pg_catalog.setval('ussd_sessions_id_seq', 31, true);


--
-- Name: smsqueues_message_md5_key; Type: CONSTRAINT; Schema: public; Owner: digital; Tablespace: 
--

ALTER TABLE ONLY smsqueues
    ADD CONSTRAINT smsqueues_message_md5_key UNIQUE (message_md5);


--
-- Name: smsqueues_pkey; Type: CONSTRAINT; Schema: public; Owner: digital; Tablespace: 
--

ALTER TABLE ONLY smsqueues
    ADD CONSTRAINT smsqueues_pkey PRIMARY KEY (id);


--
-- Name: telephone_servicecode; Type: CONSTRAINT; Schema: public; Owner: digital; Tablespace: 
--

ALTER TABLE ONLY test_app
    ADD CONSTRAINT telephone_servicecode UNIQUE (servicecode, telephone);


--
-- Name: test_app_pkey; Type: CONSTRAINT; Schema: public; Owner: digital; Tablespace: 
--

ALTER TABLE ONLY test_app
    ADD CONSTRAINT test_app_pkey PRIMARY KEY (id);


--
-- Name: ussd_sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: digital; Tablespace: 
--

ALTER TABLE ONLY ussd_sessions
    ADD CONSTRAINT ussd_sessions_pkey PRIMARY KEY (id);


--
-- Name: public; Type: ACL; Schema: -; Owner: postgres
--

REVOKE ALL ON SCHEMA public FROM PUBLIC;
REVOKE ALL ON SCHEMA public FROM postgres;
GRANT ALL ON SCHEMA public TO postgres;
GRANT ALL ON SCHEMA public TO PUBLIC;


--
-- PostgreSQL database dump complete
--

