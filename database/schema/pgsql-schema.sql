--
-- PostgreSQL database dump
--

\restrict 5tosWSwsEiV9ky3uXLBiHBVal8D1FSyv9egGMtSR6fm9XcQiqbammK11uIpUviK

-- Dumped from database version 18.1
-- Dumped by pg_dump version 18.1

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: btree_gin; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS btree_gin WITH SCHEMA public;


--
-- Name: EXTENSION btree_gin; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION btree_gin IS 'support for indexing common datatypes in GIN';


--
-- Name: citext; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS citext WITH SCHEMA public;


--
-- Name: EXTENSION citext; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION citext IS 'data type for case-insensitive character strings';


--
-- Name: pg_trgm; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pg_trgm WITH SCHEMA public;


--
-- Name: EXTENSION pg_trgm; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION pg_trgm IS 'text similarity measurement and index searching based on trigrams';


--
-- Name: pgcrypto; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pgcrypto WITH SCHEMA public;


--
-- Name: EXTENSION pgcrypto; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION pgcrypto IS 'cryptographic functions';


--
-- Name: uuid-ossp; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS "uuid-ossp" WITH SCHEMA public;


--
-- Name: EXTENSION "uuid-ossp"; Type: COMMENT; Schema: -; Owner: -
--

COMMENT ON EXTENSION "uuid-ossp" IS 'generate universally unique identifiers (UUIDs)';


--
-- Name: bloquear_delete_fisico_pacientes(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.bloquear_delete_fisico_pacientes() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
            BEGIN
                RAISE EXCEPTION 'DELETE físico não permitido em pacientes';
            END;
            $$;


--
-- Name: pacientes_incrementar_versao(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.pacientes_incrementar_versao() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
            BEGIN
                NEW.versao_registro = OLD.versao_registro + 1;
                RETURN NEW;
            END;
            $$;


--
-- Name: pacientes_log_update(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.pacientes_log_update() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
            BEGIN
                INSERT INTO pacientes_historico (
                    paciente_id,
                    dados_anteriores,
                    versao_registro,
                    alterado_em
                )
                VALUES (
                    OLD.id,
                    row_to_json(OLD),
                    OLD.versao_registro,
                    NOW()
                );
                RETURN NEW;
            END;
            $$;


--
-- Name: registrar_acesso_sensivel(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.registrar_acesso_sensivel() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
            BEGIN
                UPDATE pacientes
                SET ultimo_acesso_sensivel = NOW()
                WHERE id = NEW.id;
                RETURN NEW;
            END;
            $$;


--
-- Name: registrar_historico_paciente(); Type: FUNCTION; Schema: public; Owner: -
--

CREATE FUNCTION public.registrar_historico_paciente() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
            BEGIN
                INSERT INTO pacientes_historico (
                    paciente_id,
                    user_id,
                    acao,
                    dados_anteriores,
                    criado_em
                )
                VALUES (
                    OLD.id,
                    NULL,
                    TG_OP,
                    row_to_json(OLD),
                    NOW()
                );
                RETURN OLD;
            END;
            $$;


SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: atendimentos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.atendimentos (
    id bigint NOT NULL,
    paciente_id bigint NOT NULL,
    user_id bigint,
    unidade_id bigint,
    tipo character varying(191) NOT NULL,
    descricao text,
    status character varying(191) DEFAULT 'Aberto'::character varying NOT NULL,
    subjetivo text NOT NULL,
    objetivo text NOT NULL,
    avaliacao text NOT NULL,
    plano text NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone,
    sigtap_id bigint,
    competencia date
);


--
-- Name: atendimentos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.atendimentos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: atendimentos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.atendimentos_id_seq OWNED BY public.atendimentos.id;


--
-- Name: atendimentos_soap; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.atendimentos_soap (
    id bigint NOT NULL,
    atendimento_id bigint,
    subjetivo text,
    objetivo text,
    avaliacao text,
    plano text,
    user_id bigint,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: atendimentos_soap_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.atendimentos_soap_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: atendimentos_soap_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.atendimentos_soap_id_seq OWNED BY public.atendimentos_soap.id;


--
-- Name: auditorias; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.auditorias (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    acao character varying(191) NOT NULL,
    modulo character varying(191) NOT NULL,
    registro_id bigint,
    ip inet,
    user_agent text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: auditorias_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.auditorias_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: auditorias_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.auditorias_id_seq OWNED BY public.auditorias.id;


--
-- Name: blocked_access; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.blocked_access (
    id bigint NOT NULL,
    ip character varying(255),
    user_id character varying(255),
    country character varying(5),
    reason character varying(255) NOT NULL,
    blocked_until timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: blocked_access_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.blocked_access_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: blocked_access_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.blocked_access_id_seq OWNED BY public.blocked_access.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
    key character varying(191) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache_locks (
    key character varying(191) NOT NULL,
    owner character varying(191) NOT NULL,
    expiration integer NOT NULL
);


--
-- Name: chamadas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.chamadas (
    id bigint NOT NULL,
    nome character varying(191) NOT NULL,
    sala character varying(191) NOT NULL,
    tipo_atendimento character varying(191) DEFAULT 'consultorio'::character varying NOT NULL,
    prioridade boolean DEFAULT false NOT NULL,
    medico character varying(191),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: chamadas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.chamadas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: chamadas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.chamadas_id_seq OWNED BY public.chamadas.id;


--
-- Name: clinico_atendimentos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.clinico_atendimentos (
    id bigint NOT NULL,
    paciente_id bigint NOT NULL,
    profissional_id bigint NOT NULL,
    unidade_id bigint NOT NULL,
    tipo_atendimento character varying(191),
    queixa_principal text,
    avaliacao text,
    conduta text,
    status character varying(191) DEFAULT 'aberto'::character varying NOT NULL,
    data_atendimento timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


--
-- Name: clinico_atendimentos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.clinico_atendimentos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: clinico_atendimentos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.clinico_atendimentos_id_seq OWNED BY public.clinico_atendimentos.id;


--
-- Name: cnes_cbo; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cnes_cbo (
    id bigint NOT NULL,
    codigo character varying(10) NOT NULL,
    descricao character varying(191) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: cnes_cbo_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cnes_cbo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cnes_cbo_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cnes_cbo_id_seq OWNED BY public.cnes_cbo.id;


--
-- Name: cnes_equipes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cnes_equipes (
    id bigint NOT NULL,
    codigo_equipe character varying(20) NOT NULL,
    tipo_equipe character varying(191),
    cnes character varying(10),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: cnes_equipes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cnes_equipes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cnes_equipes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cnes_equipes_id_seq OWNED BY public.cnes_equipes.id;


--
-- Name: cnes_estabelecimentos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cnes_estabelecimentos (
    id bigint NOT NULL,
    cnes character varying(10) NOT NULL,
    nome_fantasia character varying(191),
    razao_social character varying(191),
    municipio character varying(191),
    codigo_municipio character varying(10),
    uf character varying(2) DEFAULT 'PA'::character varying NOT NULL,
    natureza_juridica character varying(191),
    tipo_unidade character varying(191),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: cnes_estabelecimentos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cnes_estabelecimentos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cnes_estabelecimentos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cnes_estabelecimentos_id_seq OWNED BY public.cnes_estabelecimentos.id;


--
-- Name: cnes_profissionais; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cnes_profissionais (
    id bigint NOT NULL,
    cpf character varying(14) NOT NULL,
    nome character varying(191) NOT NULL,
    cbo character varying(10),
    cnes character varying(10),
    tipo_vinculo character varying(191),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: cnes_profissionais_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cnes_profissionais_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cnes_profissionais_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cnes_profissionais_id_seq OWNED BY public.cnes_profissionais.id;


--
-- Name: cnes_unidades; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cnes_unidades (
    id bigint NOT NULL,
    cnes character varying(10) NOT NULL,
    nome_fantasia character varying(191),
    razao_social character varying(191),
    municipio character varying(191) NOT NULL,
    uf character varying(2) NOT NULL,
    tipo_unidade character varying(191),
    natureza_juridica character varying(191),
    telefone character varying(191),
    email character varying(191),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: cnes_unidades_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cnes_unidades_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cnes_unidades_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cnes_unidades_id_seq OWNED BY public.cnes_unidades.id;


--
-- Name: configuracoes_municipais; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.configuracoes_municipais (
    id bigint NOT NULL,
    municipio character varying(191) NOT NULL,
    uf character varying(2) NOT NULL,
    cnes_padrao character varying(191) NOT NULL,
    versao_layout character varying(191) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: configuracoes_municipais_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.configuracoes_municipais_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: configuracoes_municipais_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.configuracoes_municipais_id_seq OWNED BY public.configuracoes_municipais.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(191) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: historico_usuarios; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.historico_usuarios (
    id bigint NOT NULL,
    nome character varying(191) NOT NULL,
    email character varying(191) NOT NULL,
    telefone character varying(191),
    cpf character varying(191),
    cargo character varying(191),
    data_nascimento date,
    endereco character varying(191),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: historico_usuarios_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.historico_usuarios_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: historico_usuarios_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.historico_usuarios_id_seq OWNED BY public.historico_usuarios.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_batches (
    id character varying(191) NOT NULL,
    name character varying(191) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(191) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: logs_acesso; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.logs_acesso (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    acao character varying(191) NOT NULL,
    ip character varying(191) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: logs_acesso_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.logs_acesso_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: logs_acesso_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.logs_acesso_id_seq OWNED BY public.logs_acesso.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(191) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: pacientes; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pacientes (
    id bigint NOT NULL,
    nome character varying(255) NOT NULL,
    cpf_hash character varying(64) NOT NULL,
    cpf character varying(191),
    email_hash character varying(64),
    email text,
    rg_hash character varying(64),
    rg text,
    cns_hash character varying(64),
    cns text,
    telefone text,
    nome_mae text,
    data_nascimento date,
    sexo character varying(191),
    cep character varying(10),
    logradouro character varying(255),
    numero character varying(20),
    bairro character varying(100),
    municipio character varying(100),
    uf character varying(2),
    raca_cor character varying(50),
    estado_civil character varying(50),
    nacionalidade character varying(100),
    prioridade smallint DEFAULT '0'::smallint NOT NULL,
    prioridade_motivo character varying(255),
    created_by bigint,
    updated_by bigint,
    deleted_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    pulseira character varying(191),
    tempo_maximo integer,
    hora_triagem timestamp(0) without time zone,
    alerta_social boolean DEFAULT false NOT NULL,
    triagem_registrada boolean DEFAULT false NOT NULL,
    ativo boolean DEFAULT true NOT NULL,
    anonimizado boolean DEFAULT false NOT NULL,
    versao_registro bigint DEFAULT '1'::bigint NOT NULL,
    ultimo_acesso_sensivel timestamp(0) without time zone,
    CONSTRAINT versao_nao_regressiva CHECK ((versao_registro >= 1)),
    CONSTRAINT versao_registro_positiva_check CHECK ((versao_registro >= 1))
);

ALTER TABLE ONLY public.pacientes FORCE ROW LEVEL SECURITY;


--
-- Name: pacientes_auditoria; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pacientes_auditoria (
    id bigint NOT NULL,
    paciente_id bigint NOT NULL,
    coluna character varying(191) NOT NULL,
    valor_anterior text,
    valor_novo text,
    alterado_por bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: pacientes_auditoria_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.pacientes_auditoria_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pacientes_auditoria_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.pacientes_auditoria_id_seq OWNED BY public.pacientes_auditoria.id;


--
-- Name: pacientes_historico; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.pacientes_historico (
    id bigint NOT NULL,
    paciente_id bigint NOT NULL,
    user_id bigint,
    acao character varying(20) NOT NULL,
    dados_anteriores jsonb NOT NULL,
    criado_em timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: pacientes_historico_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.pacientes_historico_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pacientes_historico_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.pacientes_historico_id_seq OWNED BY public.pacientes_historico.id;


--
-- Name: pacientes_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.pacientes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: pacientes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.pacientes_id_seq OWNED BY public.pacientes.id;


--
-- Name: password_resets; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_resets (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: permissions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.permissions (
    id bigint NOT NULL,
    nome character varying(191) NOT NULL,
    descricao character varying(191),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: permissions_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.permissions_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: permissions_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.permissions_id_seq OWNED BY public.permissions.id;


--
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(191) NOT NULL,
    tokenable_id bigint NOT NULL,
    name text NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- Name: roles; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.roles (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    description character varying(255),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: roles_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.roles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: roles_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.roles_id_seq OWNED BY public.roles.id;


--
-- Name: senhas; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.senhas (
    id bigint NOT NULL,
    numero character varying(191) NOT NULL,
    prioridade character varying(191) DEFAULT 'Normal'::character varying NOT NULL,
    chamada boolean DEFAULT false NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: senhas_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.senhas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: senhas_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.senhas_id_seq OWNED BY public.senhas.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions (
    id character varying(191) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: sigtap_procedimentos; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sigtap_procedimentos (
    id bigint NOT NULL,
    codigo character varying(20) NOT NULL,
    nome character varying(255) NOT NULL,
    complexidade character varying(50),
    tipo_financiamento character varying(50),
    ativo boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: sigtap_procedimentos_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.sigtap_procedimentos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: sigtap_procedimentos_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.sigtap_procedimentos_id_seq OWNED BY public.sigtap_procedimentos.id;


--
-- Name: unidades; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.unidades (
    id bigint NOT NULL,
    nome character varying(191) NOT NULL,
    cnes character varying(191),
    tipo character varying(191),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: unidades_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.unidades_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: unidades_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.unidades_id_seq OWNED BY public.unidades.id;


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(191) NOT NULL,
    email character varying(191) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(191) NOT NULL,
    perfil character varying(191),
    role character varying(191) DEFAULT 'usuario'::character varying NOT NULL,
    sala character varying(191),
    unidade_id bigint,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    deleted_at timestamp(0) without time zone
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: atendimentos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.atendimentos ALTER COLUMN id SET DEFAULT nextval('public.atendimentos_id_seq'::regclass);


--
-- Name: atendimentos_soap id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.atendimentos_soap ALTER COLUMN id SET DEFAULT nextval('public.atendimentos_soap_id_seq'::regclass);


--
-- Name: auditorias id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.auditorias ALTER COLUMN id SET DEFAULT nextval('public.auditorias_id_seq'::regclass);


--
-- Name: blocked_access id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.blocked_access ALTER COLUMN id SET DEFAULT nextval('public.blocked_access_id_seq'::regclass);


--
-- Name: chamadas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.chamadas ALTER COLUMN id SET DEFAULT nextval('public.chamadas_id_seq'::regclass);


--
-- Name: clinico_atendimentos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.clinico_atendimentos ALTER COLUMN id SET DEFAULT nextval('public.clinico_atendimentos_id_seq'::regclass);


--
-- Name: cnes_cbo id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cnes_cbo ALTER COLUMN id SET DEFAULT nextval('public.cnes_cbo_id_seq'::regclass);


--
-- Name: cnes_equipes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cnes_equipes ALTER COLUMN id SET DEFAULT nextval('public.cnes_equipes_id_seq'::regclass);


--
-- Name: cnes_estabelecimentos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cnes_estabelecimentos ALTER COLUMN id SET DEFAULT nextval('public.cnes_estabelecimentos_id_seq'::regclass);


--
-- Name: cnes_profissionais id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cnes_profissionais ALTER COLUMN id SET DEFAULT nextval('public.cnes_profissionais_id_seq'::regclass);


--
-- Name: cnes_unidades id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cnes_unidades ALTER COLUMN id SET DEFAULT nextval('public.cnes_unidades_id_seq'::regclass);


--
-- Name: configuracoes_municipais id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.configuracoes_municipais ALTER COLUMN id SET DEFAULT nextval('public.configuracoes_municipais_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: historico_usuarios id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.historico_usuarios ALTER COLUMN id SET DEFAULT nextval('public.historico_usuarios_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: logs_acesso id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logs_acesso ALTER COLUMN id SET DEFAULT nextval('public.logs_acesso_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: pacientes id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pacientes ALTER COLUMN id SET DEFAULT nextval('public.pacientes_id_seq'::regclass);


--
-- Name: pacientes_auditoria id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pacientes_auditoria ALTER COLUMN id SET DEFAULT nextval('public.pacientes_auditoria_id_seq'::regclass);


--
-- Name: pacientes_historico id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pacientes_historico ALTER COLUMN id SET DEFAULT nextval('public.pacientes_historico_id_seq'::regclass);


--
-- Name: permissions id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permissions ALTER COLUMN id SET DEFAULT nextval('public.permissions_id_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Name: roles id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles ALTER COLUMN id SET DEFAULT nextval('public.roles_id_seq'::regclass);


--
-- Name: senhas id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.senhas ALTER COLUMN id SET DEFAULT nextval('public.senhas_id_seq'::regclass);


--
-- Name: sigtap_procedimentos id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sigtap_procedimentos ALTER COLUMN id SET DEFAULT nextval('public.sigtap_procedimentos_id_seq'::regclass);


--
-- Name: unidades id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unidades ALTER COLUMN id SET DEFAULT nextval('public.unidades_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: atendimentos atendimentos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.atendimentos
    ADD CONSTRAINT atendimentos_pkey PRIMARY KEY (id);


--
-- Name: atendimentos_soap atendimentos_soap_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.atendimentos_soap
    ADD CONSTRAINT atendimentos_soap_pkey PRIMARY KEY (id);


--
-- Name: auditorias auditorias_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.auditorias
    ADD CONSTRAINT auditorias_pkey PRIMARY KEY (id);


--
-- Name: blocked_access blocked_access_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.blocked_access
    ADD CONSTRAINT blocked_access_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: chamadas chamadas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.chamadas
    ADD CONSTRAINT chamadas_pkey PRIMARY KEY (id);


--
-- Name: clinico_atendimentos clinico_atendimentos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.clinico_atendimentos
    ADD CONSTRAINT clinico_atendimentos_pkey PRIMARY KEY (id);


--
-- Name: cnes_cbo cnes_cbo_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cnes_cbo
    ADD CONSTRAINT cnes_cbo_codigo_unique UNIQUE (codigo);


--
-- Name: cnes_cbo cnes_cbo_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cnes_cbo
    ADD CONSTRAINT cnes_cbo_pkey PRIMARY KEY (id);


--
-- Name: cnes_equipes cnes_equipes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cnes_equipes
    ADD CONSTRAINT cnes_equipes_pkey PRIMARY KEY (id);


--
-- Name: cnes_estabelecimentos cnes_estabelecimentos_cnes_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cnes_estabelecimentos
    ADD CONSTRAINT cnes_estabelecimentos_cnes_unique UNIQUE (cnes);


--
-- Name: cnes_estabelecimentos cnes_estabelecimentos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cnes_estabelecimentos
    ADD CONSTRAINT cnes_estabelecimentos_pkey PRIMARY KEY (id);


--
-- Name: cnes_profissionais cnes_profissionais_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cnes_profissionais
    ADD CONSTRAINT cnes_profissionais_pkey PRIMARY KEY (id);


--
-- Name: cnes_unidades cnes_unidades_cnes_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cnes_unidades
    ADD CONSTRAINT cnes_unidades_cnes_unique UNIQUE (cnes);


--
-- Name: cnes_unidades cnes_unidades_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cnes_unidades
    ADD CONSTRAINT cnes_unidades_pkey PRIMARY KEY (id);


--
-- Name: configuracoes_municipais configuracoes_municipais_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.configuracoes_municipais
    ADD CONSTRAINT configuracoes_municipais_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: historico_usuarios historico_usuarios_cpf_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.historico_usuarios
    ADD CONSTRAINT historico_usuarios_cpf_unique UNIQUE (cpf);


--
-- Name: historico_usuarios historico_usuarios_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.historico_usuarios
    ADD CONSTRAINT historico_usuarios_email_unique UNIQUE (email);


--
-- Name: historico_usuarios historico_usuarios_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.historico_usuarios
    ADD CONSTRAINT historico_usuarios_pkey PRIMARY KEY (id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: logs_acesso logs_acesso_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logs_acesso
    ADD CONSTRAINT logs_acesso_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: pacientes_auditoria pacientes_auditoria_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pacientes_auditoria
    ADD CONSTRAINT pacientes_auditoria_pkey PRIMARY KEY (id);


--
-- Name: pacientes pacientes_cpf_hash_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pacientes
    ADD CONSTRAINT pacientes_cpf_hash_unique UNIQUE (cpf_hash);


--
-- Name: pacientes pacientes_email_hash_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pacientes
    ADD CONSTRAINT pacientes_email_hash_unique UNIQUE (email_hash);


--
-- Name: pacientes_historico pacientes_historico_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pacientes_historico
    ADD CONSTRAINT pacientes_historico_pkey PRIMARY KEY (id);


--
-- Name: pacientes pacientes_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pacientes
    ADD CONSTRAINT pacientes_pkey PRIMARY KEY (id);


--
-- Name: permissions permissions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.permissions
    ADD CONSTRAINT permissions_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- Name: roles roles_name_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_name_unique UNIQUE (name);


--
-- Name: roles roles_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.roles
    ADD CONSTRAINT roles_pkey PRIMARY KEY (id);


--
-- Name: senhas senhas_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.senhas
    ADD CONSTRAINT senhas_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: sigtap_procedimentos sigtap_procedimentos_codigo_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sigtap_procedimentos
    ADD CONSTRAINT sigtap_procedimentos_codigo_unique UNIQUE (codigo);


--
-- Name: sigtap_procedimentos sigtap_procedimentos_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sigtap_procedimentos
    ADD CONSTRAINT sigtap_procedimentos_pkey PRIMARY KEY (id);


--
-- Name: unidades unidades_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.unidades
    ADD CONSTRAINT unidades_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: atendimentos_competencia_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX atendimentos_competencia_index ON public.atendimentos USING btree (competencia);


--
-- Name: blocked_access_blocked_until_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX blocked_access_blocked_until_index ON public.blocked_access USING btree (blocked_until);


--
-- Name: blocked_access_country_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX blocked_access_country_index ON public.blocked_access USING btree (country);


--
-- Name: blocked_access_ip_blocked_until_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX blocked_access_ip_blocked_until_index ON public.blocked_access USING btree (ip, blocked_until);


--
-- Name: blocked_access_ip_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX blocked_access_ip_index ON public.blocked_access USING btree (ip);


--
-- Name: blocked_access_user_id_blocked_until_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX blocked_access_user_id_blocked_until_index ON public.blocked_access USING btree (user_id, blocked_until);


--
-- Name: blocked_access_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX blocked_access_user_id_index ON public.blocked_access USING btree (user_id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: chamadas_medico_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX chamadas_medico_index ON public.chamadas USING btree (medico);


--
-- Name: chamadas_nome_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX chamadas_nome_index ON public.chamadas USING btree (nome);


--
-- Name: chamadas_prioridade_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX chamadas_prioridade_created_at_index ON public.chamadas USING btree (prioridade, created_at);


--
-- Name: chamadas_prioridade_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX chamadas_prioridade_index ON public.chamadas USING btree (prioridade);


--
-- Name: chamadas_sala_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX chamadas_sala_index ON public.chamadas USING btree (sala);


--
-- Name: chamadas_tipo_atendimento_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX chamadas_tipo_atendimento_index ON public.chamadas USING btree (tipo_atendimento);


--
-- Name: clinico_atendimentos_data_atendimento_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX clinico_atendimentos_data_atendimento_index ON public.clinico_atendimentos USING btree (data_atendimento);


--
-- Name: clinico_atendimentos_paciente_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX clinico_atendimentos_paciente_id_index ON public.clinico_atendimentos USING btree (paciente_id);


--
-- Name: clinico_atendimentos_profissional_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX clinico_atendimentos_profissional_id_index ON public.clinico_atendimentos USING btree (profissional_id);


--
-- Name: clinico_atendimentos_status_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX clinico_atendimentos_status_index ON public.clinico_atendimentos USING btree (status);


--
-- Name: clinico_atendimentos_unidade_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX clinico_atendimentos_unidade_id_index ON public.clinico_atendimentos USING btree (unidade_id);


--
-- Name: cnes_cbo_codigo_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cnes_cbo_codigo_index ON public.cnes_cbo USING btree (codigo);


--
-- Name: cnes_equipes_cnes_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cnes_equipes_cnes_index ON public.cnes_equipes USING btree (cnes);


--
-- Name: cnes_equipes_codigo_equipe_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cnes_equipes_codigo_equipe_index ON public.cnes_equipes USING btree (codigo_equipe);


--
-- Name: cnes_estabelecimentos_cnes_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cnes_estabelecimentos_cnes_index ON public.cnes_estabelecimentos USING btree (cnes);


--
-- Name: cnes_estabelecimentos_codigo_municipio_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cnes_estabelecimentos_codigo_municipio_index ON public.cnes_estabelecimentos USING btree (codigo_municipio);


--
-- Name: cnes_profissionais_cbo_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cnes_profissionais_cbo_index ON public.cnes_profissionais USING btree (cbo);


--
-- Name: cnes_profissionais_cnes_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cnes_profissionais_cnes_index ON public.cnes_profissionais USING btree (cnes);


--
-- Name: cnes_profissionais_cpf_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cnes_profissionais_cpf_index ON public.cnes_profissionais USING btree (cpf);


--
-- Name: cnes_unidades_municipio_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cnes_unidades_municipio_index ON public.cnes_unidades USING btree (municipio);


--
-- Name: cnes_unidades_municipio_uf_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cnes_unidades_municipio_uf_index ON public.cnes_unidades USING btree (municipio, uf);


--
-- Name: cnes_unidades_uf_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cnes_unidades_uf_index ON public.cnes_unidades USING btree (uf);


--
-- Name: idx_atendimentos_status; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_atendimentos_status ON public.atendimentos USING btree (status);


--
-- Name: idx_atendimentos_unidade_status; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_atendimentos_unidade_status ON public.atendimentos USING btree (unidade_id, status);


--
-- Name: idx_atendimentos_user_status; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX idx_atendimentos_user_status ON public.atendimentos USING btree (user_id, status);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: logs_acesso_acao_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX logs_acesso_acao_index ON public.logs_acesso USING btree (acao);


--
-- Name: logs_acesso_ip_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX logs_acesso_ip_index ON public.logs_acesso USING btree (ip);


--
-- Name: logs_acesso_user_id_created_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX logs_acesso_user_id_created_at_index ON public.logs_acesso USING btree (user_id, created_at);


--
-- Name: pacientes_anonimizado_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_anonimizado_idx ON public.pacientes USING btree (anonimizado);


--
-- Name: pacientes_ativo_deleted_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_ativo_deleted_idx ON public.pacientes USING btree (ativo, deleted_at);


--
-- Name: pacientes_ativo_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_ativo_idx ON public.pacientes USING btree (ativo);


--
-- Name: pacientes_ativos_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_ativos_idx ON public.pacientes USING btree (municipio, uf) WHERE ((ativo = true) AND (deleted_at IS NULL));


--
-- Name: pacientes_ativos_municipio_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_ativos_municipio_idx ON public.pacientes USING btree (municipio, uf) WHERE ((ativo = true) AND (deleted_at IS NULL));


--
-- Name: pacientes_bairro_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_bairro_index ON public.pacientes USING btree (bairro);


--
-- Name: pacientes_cep_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_cep_index ON public.pacientes USING btree (cep);


--
-- Name: pacientes_cns_hash_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_cns_hash_index ON public.pacientes USING btree (cns_hash);


--
-- Name: pacientes_created_by_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_created_by_idx ON public.pacientes USING btree (created_by, created_at) WHERE (deleted_at IS NULL);


--
-- Name: pacientes_data_nascimento_brin; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_data_nascimento_brin ON public.pacientes USING brin (data_nascimento);


--
-- Name: pacientes_data_nascimento_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_data_nascimento_index ON public.pacientes USING btree (data_nascimento);


--
-- Name: pacientes_deleted_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_deleted_at_index ON public.pacientes USING btree (deleted_at);


--
-- Name: pacientes_lookup_hash_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_lookup_hash_idx ON public.pacientes USING btree (cpf_hash, cns_hash);


--
-- Name: pacientes_municipio_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_municipio_index ON public.pacientes USING btree (municipio);


--
-- Name: pacientes_nome_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_nome_index ON public.pacientes USING btree (nome);


--
-- Name: pacientes_prioridade_ativa_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_prioridade_ativa_idx ON public.pacientes USING btree (prioridade) WHERE ((ativo = true) AND (deleted_at IS NULL));


--
-- Name: pacientes_prioridade_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_prioridade_idx ON public.pacientes USING btree (prioridade) WHERE (ativo = true);


--
-- Name: pacientes_prioridade_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_prioridade_index ON public.pacientes USING btree (prioridade);


--
-- Name: pacientes_rg_hash_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_rg_hash_index ON public.pacientes USING btree (rg_hash);


--
-- Name: pacientes_uf_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_uf_index ON public.pacientes USING btree (uf);


--
-- Name: pacientes_ultimo_acesso_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_ultimo_acesso_idx ON public.pacientes USING btree (ultimo_acesso_sensivel);


--
-- Name: pacientes_updated_by_idx; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX pacientes_updated_by_idx ON public.pacientes USING btree (updated_by, updated_at) WHERE (deleted_at IS NULL);


--
-- Name: password_resets_email_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX password_resets_email_index ON public.password_resets USING btree (email);


--
-- Name: personal_access_tokens_expires_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX personal_access_tokens_expires_at_index ON public.personal_access_tokens USING btree (expires_at);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: sigtap_procedimentos_ativo_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sigtap_procedimentos_ativo_index ON public.sigtap_procedimentos USING btree (ativo);


--
-- Name: sigtap_procedimentos_codigo_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sigtap_procedimentos_codigo_index ON public.sigtap_procedimentos USING btree (codigo);


--
-- Name: pacientes pacientes_block_delete; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER pacientes_block_delete BEFORE DELETE ON public.pacientes FOR EACH ROW EXECUTE FUNCTION public.bloquear_delete_fisico_pacientes();


--
-- Name: pacientes pacientes_log_update_trigger; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER pacientes_log_update_trigger BEFORE UPDATE ON public.pacientes FOR EACH ROW EXECUTE FUNCTION public.pacientes_log_update();


--
-- Name: pacientes pacientes_trigger_incrementar_versao; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER pacientes_trigger_incrementar_versao BEFORE UPDATE ON public.pacientes FOR EACH ROW EXECUTE FUNCTION public.pacientes_incrementar_versao();


--
-- Name: pacientes trigger_historico_delete; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trigger_historico_delete BEFORE DELETE ON public.pacientes FOR EACH ROW EXECUTE FUNCTION public.registrar_historico_paciente();


--
-- Name: pacientes trigger_historico_update; Type: TRIGGER; Schema: public; Owner: -
--

CREATE TRIGGER trigger_historico_update BEFORE UPDATE ON public.pacientes FOR EACH ROW EXECUTE FUNCTION public.registrar_historico_paciente();


--
-- Name: logs_acesso 1; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.logs_acesso
    ADD CONSTRAINT "1" FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: atendimentos atendimentos_paciente_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.atendimentos
    ADD CONSTRAINT atendimentos_paciente_id_foreign FOREIGN KEY (paciente_id) REFERENCES public.pacientes(id) ON DELETE CASCADE;


--
-- Name: atendimentos atendimentos_sigtap_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.atendimentos
    ADD CONSTRAINT atendimentos_sigtap_id_foreign FOREIGN KEY (sigtap_id) REFERENCES public.sigtap_procedimentos(id) ON DELETE SET NULL;


--
-- Name: atendimentos_soap atendimentos_soap_atendimento_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.atendimentos_soap
    ADD CONSTRAINT atendimentos_soap_atendimento_id_foreign FOREIGN KEY (atendimento_id) REFERENCES public.clinico_atendimentos(id) ON DELETE CASCADE;


--
-- Name: atendimentos_soap atendimentos_soap_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.atendimentos_soap
    ADD CONSTRAINT atendimentos_soap_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: atendimentos atendimentos_unidade_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.atendimentos
    ADD CONSTRAINT atendimentos_unidade_id_foreign FOREIGN KEY (unidade_id) REFERENCES public.unidades(id) ON DELETE CASCADE;


--
-- Name: atendimentos atendimentos_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.atendimentos
    ADD CONSTRAINT atendimentos_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: auditorias auditorias_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.auditorias
    ADD CONSTRAINT auditorias_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: clinico_atendimentos clinico_atendimentos_paciente_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.clinico_atendimentos
    ADD CONSTRAINT clinico_atendimentos_paciente_id_foreign FOREIGN KEY (paciente_id) REFERENCES public.pacientes(id) ON DELETE CASCADE;


--
-- Name: clinico_atendimentos clinico_atendimentos_profissional_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.clinico_atendimentos
    ADD CONSTRAINT clinico_atendimentos_profissional_id_foreign FOREIGN KEY (profissional_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: clinico_atendimentos clinico_atendimentos_unidade_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.clinico_atendimentos
    ADD CONSTRAINT clinico_atendimentos_unidade_id_foreign FOREIGN KEY (unidade_id) REFERENCES public.unidades(id) ON DELETE CASCADE;


--
-- Name: pacientes_auditoria pacientes_auditoria_paciente_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pacientes_auditoria
    ADD CONSTRAINT pacientes_auditoria_paciente_id_foreign FOREIGN KEY (paciente_id) REFERENCES public.pacientes(id);


--
-- Name: pacientes pacientes_created_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pacientes
    ADD CONSTRAINT pacientes_created_by_foreign FOREIGN KEY (created_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: pacientes_historico pacientes_historico_paciente_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pacientes_historico
    ADD CONSTRAINT pacientes_historico_paciente_id_foreign FOREIGN KEY (paciente_id) REFERENCES public.pacientes(id) ON DELETE CASCADE;


--
-- Name: pacientes pacientes_updated_by_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.pacientes
    ADD CONSTRAINT pacientes_updated_by_foreign FOREIGN KEY (updated_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: users users_unidade_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_unidade_id_foreign FOREIGN KEY (unidade_id) REFERENCES public.unidades(id) ON DELETE SET NULL;


--
-- Name: pacientes; Type: ROW SECURITY; Schema: public; Owner: -
--

ALTER TABLE public.pacientes ENABLE ROW LEVEL SECURITY;

--
-- Name: pacientes pacientes_default_deny; Type: POLICY; Schema: public; Owner: -
--

CREATE POLICY pacientes_default_deny ON public.pacientes USING (false);


--
-- Name: pacientes pacientes_nao_deletados; Type: POLICY; Schema: public; Owner: -
--

CREATE POLICY pacientes_nao_deletados ON public.pacientes FOR SELECT USING ((deleted_at IS NULL));


--
-- PostgreSQL database dump complete
--

\unrestrict 5tosWSwsEiV9ky3uXLBiHBVal8D1FSyv9egGMtSR6fm9XcQiqbammK11uIpUviK

--
-- PostgreSQL database dump
--

\restrict eb4nqDZgZdpHIlMUmJlyccHRKUT0TWh3kfrv4rAMBTE0qk5BCvYcrMRHtolYt5e

-- Dumped from database version 18.1
-- Dumped by pg_dump version 18.1

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.migrations (id, migration, batch) FROM stdin;
43	2014_10_12_100000_create_password_resets_table	1
44	2026_04_06_000000_create_postgres_extensions	1
45	2026_04_06_000001_create_roles_table	1
46	2026_04_06_000002_create_users_table	1
47	2026_04_06_000003_create_permissions_table	1
48	2026_04_06_000004_create_role_user_table	1
49	2026_04_06_000005_create_permission_role_table	1
50	2026_04_06_000006_create_estados_table	1
51	2026_04_06_000006_create_unidades_table	1
52	2026_04_06_000007_create_municipios_table	1
53	2026_04_06_000008_create_pacientes_table	1
54	2026_04_06_000009_create_profissionais_table	1
55	2026_04_06_000011_create_atendimentos_table	1
56	2026_04_06_000012_create_triagens_table	1
57	2026_04_06_000013_create_prescricoes_table	1
58	2026_04_06_000014_create_logs_auditoria_table	1
59	2026_04_06_201135_add_indexes_all_tables	1
60	2026_04_07_013621_create_postgres_extensions	1
61	2026_04_08_000019_create_fila_notificacoes_table	1
62	2026_04_09_000020_update_fila_notificacoes_table	1
63	2026_04_09_000021_create_registro_multiprofissional_table	1
64	2026_04_09_000022_create_domicilios_table	1
65	2026_04_09_000023_create_familias_table	1
66	2026_04_09_000024_create_visitas_domiciliares_table	1
67	2026_04_10_000015_create_auditorias_table	1
68	2026_04_10_000025_create_sisab_envios_table	1
69	2026_04_10_000026_create_familia_pessoas_table	1
70	2026_04_10_000027_create_territorializacoes_table	1
71	2026_04_10_000028_create_eventos_sistema_table	1
72	2026_04_10_000029_create_acessos_emergenciais_table	1
73	2026_04_10_000030_add_hash_to_eventos_sistema_table	1
74	2026_04_10_000031_add_coluna_x_to_tabela_y	1
75	2026_04_10_000032_add_cns_to_pacientes	1
76	2026_04_10_000033_nome_claro_da_mudanca	1
77	2026_04_12_000016_create_logs_forenses_table_create_logs_forenses_table	1
78	2026_04_12_000017_add_assinatura_to_auditorias	1
79	2026_04_12_000018_protect_logs_forenses_table	1
80	2026_04_14_211740_create_whatsapp_messages_table	1
81	2026_04_14_212818_create_failed_jobs_table	1
82	2026_04_15_175203_create_jobs_table	1
83	2026_04_22_030309_create_sessions_table	1
84	2026_04_22_030313_create_cache_table	1
85	2026_05_06_014212_create_blocked_access_table	2
\.


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 85, true);


--
-- PostgreSQL database dump complete
--

\unrestrict eb4nqDZgZdpHIlMUmJlyccHRKUT0TWh3kfrv4rAMBTE0qk5BCvYcrMRHtolYt5e

