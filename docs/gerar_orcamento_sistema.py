from __future__ import annotations

import html
import zipfile
from pathlib import Path


OUTPUT = Path(__file__).with_name("orcamento_sistema_legiscola.docx")


def esc(value: object) -> str:
    return html.escape(str(value), quote=True)


def paragraph(text: str = "", style: str | None = None, bold: bool = False) -> str:
    style_xml = f'<w:pStyle w:val="{style}"/>' if style else ""
    bold_xml = "<w:b/>" if bold else ""
    if not text:
        return "<w:p/>"
    return (
        "<w:p>"
        f"<w:pPr>{style_xml}</w:pPr>"
        "<w:r>"
        f"<w:rPr>{bold_xml}</w:rPr>"
        f"<w:t xml:space=\"preserve\">{esc(text)}</w:t>"
        "</w:r>"
        "</w:p>"
    )


def heading(text: str, level: int = 1) -> str:
    return paragraph(text, f"Heading{level}", bold=True)


def bullet(text: str) -> str:
    return (
        "<w:p>"
        "<w:pPr><w:pStyle w:val=\"ListParagraph\"/></w:pPr>"
        "<w:r><w:t>• </w:t></w:r>"
        f"<w:r><w:t xml:space=\"preserve\">{esc(text)}</w:t></w:r>"
        "</w:p>"
    )


def cell(text: str, bold: bool = False) -> str:
    bold_xml = "<w:b/>" if bold else ""
    return (
        "<w:tc>"
        "<w:tcPr><w:tcW w:w=\"2400\" w:type=\"dxa\"/></w:tcPr>"
        "<w:p><w:r>"
        f"<w:rPr>{bold_xml}</w:rPr>"
        f"<w:t xml:space=\"preserve\">{esc(text)}</w:t>"
        "</w:r></w:p>"
        "</w:tc>"
    )


def table(rows: list[list[str]], header: bool = True) -> str:
    body = [
        "<w:tbl>"
        "<w:tblPr>"
        "<w:tblStyle w:val=\"TableGrid\"/>"
        "<w:tblW w:w=\"0\" w:type=\"auto\"/>"
        "<w:tblLook w:val=\"04A0\"/>"
        "</w:tblPr>"
    ]
    for idx, row in enumerate(rows):
        body.append("<w:tr>")
        for value in row:
            body.append(cell(value, bold=header and idx == 0))
        body.append("</w:tr>")
    body.append("</w:tbl>")
    return "".join(body)


def doc_xml(content: str) -> str:
    return f"""<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:body>
    {content}
    <w:sectPr>
      <w:pgSz w:w="11906" w:h="16838"/>
      <w:pgMar w:top="1134" w:right="1134" w:bottom="1134" w:left="1134" w:header="708" w:footer="708" w:gutter="0"/>
    </w:sectPr>
  </w:body>
</w:document>
"""


def styles_xml() -> str:
    return """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:styles xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:style w:type="paragraph" w:default="1" w:styleId="Normal">
    <w:name w:val="Normal"/>
    <w:qFormat/>
    <w:rPr><w:sz w:val="22"/><w:szCs w:val="22"/></w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Title">
    <w:name w:val="Title"/>
    <w:qFormat/>
    <w:rPr><w:b/><w:sz w:val="40"/><w:szCs w:val="40"/></w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Heading1">
    <w:name w:val="heading 1"/>
    <w:basedOn w:val="Normal"/>
    <w:next w:val="Normal"/>
    <w:qFormat/>
    <w:pPr><w:spacing w:before="360" w:after="120"/></w:pPr>
    <w:rPr><w:b/><w:sz w:val="30"/><w:szCs w:val="30"/></w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="Heading2">
    <w:name w:val="heading 2"/>
    <w:basedOn w:val="Normal"/>
    <w:next w:val="Normal"/>
    <w:qFormat/>
    <w:pPr><w:spacing w:before="240" w:after="80"/></w:pPr>
    <w:rPr><w:b/><w:sz w:val="26"/><w:szCs w:val="26"/></w:rPr>
  </w:style>
  <w:style w:type="paragraph" w:styleId="ListParagraph">
    <w:name w:val="List Paragraph"/>
    <w:basedOn w:val="Normal"/>
    <w:pPr><w:ind w:left="360"/></w:pPr>
  </w:style>
  <w:style w:type="table" w:styleId="TableGrid">
    <w:name w:val="Table Grid"/>
    <w:tblPr><w:tblBorders>
      <w:top w:val="single" w:sz="4" w:space="0" w:color="999999"/>
      <w:left w:val="single" w:sz="4" w:space="0" w:color="999999"/>
      <w:bottom w:val="single" w:sz="4" w:space="0" w:color="999999"/>
      <w:right w:val="single" w:sz="4" w:space="0" w:color="999999"/>
      <w:insideH w:val="single" w:sz="4" w:space="0" w:color="999999"/>
      <w:insideV w:val="single" w:sz="4" w:space="0" w:color="999999"/>
    </w:tblBorders></w:tblPr>
  </w:style>
</w:styles>
"""


modules = [
    ["Modulo/Area", "Funcionalidades contempladas", "Observacao para valor"],
    ["Plataforma SaaS multi-tenant", "Estrutura por tenant, isolamento de dados, status de tenants, subdominio/contexto por cliente, vinculo de usuarios ao tenant.", ""],
    ["Central do Super Admin", "Dashboard central, gestao de tenants, ativacao/suspensao, usuarios por tenant, roles, permissoes e termo global de privacidade/LGPD.", ""],
    ["Autenticacao e permissoes", "Login central e tenant, cadastro, recuperacao de senha, verificacao de e-mail, aceite de termo, roles e permissoes com Spatie Permission.", ""],
    ["Portal publico do tenant", "Home, noticias, eventos, cursos, agenda, professores, sobre a escola, contato, privacidade, login/cadastro e acesso docente.", ""],
    ["Configuracoes do portal", "Identidade visual, foto de capa, cores, cidade/camara, dados institucionais e ajustes administrativos do tenant.", ""],
    ["Gestao de usuarios", "CRUD de usuarios, perfil, troca de senha, tipos de usuario e controle por papel de acesso.", ""],
    ["Cursos", "Cadastro e manutencao de cursos, busca administrativa e exibicao no portal.", ""],
    ["Turmas", "Cadastro de turmas, vagas, horarios, professores vinculados, matriculas, status de matricula, avisos e conclusao de turma.", ""],
    ["Aulas", "Cadastro de aulas, materiais anexos/download, visualizacao pelo aluno e lancamento de presenca.", ""],
    ["Alunos", "Cadastro, dados pessoais, endereco, profissao/escolaridade, foto, area do aluno, turmas matriculadas, aulas e certificados.", ""],
    ["Docentes/Professores", "Painel docente, perfil, senha, turmas, aulas, ficha de presenca, aula rapida, avisos e provas/quizzes.", ""],
    ["Ficha de presenca", "Geracao, edicao, impressao e exclusao de ficha de presenca por turma/aula, incluindo lancamentos por docente/admin.", ""],
    ["Eventos", "Cadastro de eventos, inscricoes, controle de presenca, limite de vagas, periodo de inscricao, foto, PDFs de inscritos e triagem.", ""],
    ["Certificados", "Templates de certificado, preview, emissao, download pelo aluno, validacao publica por hash e revogacao.", ""],
    ["Quizzes/Provas", "Cadastro de quizzes, perguntas, respostas, janelas por turma, tentativas, envio pelo aluno, impressao e controle de status por turma.", ""],
    ["Construtor de provas", "Tela Livewire para montagem de provas e rota de impressao de provas.", ""],
    ["Noticias", "CRUD de noticias do tenant, fotos, listagem e detalhe no portal.", ""],
    ["Contato do portal", "Formulario publico, armazenamento das mensagens, visualizacao pelo admin e resposta por e-mail.", ""],
    ["LGPD/Privacidade", "Termo global de privacidade, aceite obrigatorio por usuario, pagina de privacidade do portal e controle de versao/aceite.", ""],
    ["E-mails e filas", "E-mails de boas-vindas, verificacao, recuperacao de senha, resposta de contato e envio assincrono por queue.", ""],
    ["Auditoria e logs", "Activity Log com rastreamento de alteracoes em modelos principais e historico de eventos.", ""],
    ["Relatorios e exportacoes", "Base instalada para PDF com DomPDF e planilhas com Maatwebsite Excel/PhpSpreadsheet.", ""],
    ["API REST", "API v1 com Sanctum para login, cadastro, usuario autenticado, tenants, usuarios e budgets, preparada para futuro mobile/Ionic.", ""],
    ["Orcamentos/Budgets", "Modelo e API de orcamentos com fluxo de aprovacao/rejeicao, conforme estrutura SaaS base.", ""],
    ["Testes e qualidade", "Testes Pest, Laravel Pint, factories, seeders e estrutura para evolucao de cobertura.", ""],
]

budget_rows = [
    ["Item", "Descricao", "Qtd.", "Unid.", "Valor unitario", "Total"],
    ["1", "Levantamento, organizacao e parametrizacao inicial do sistema", "1", "servico", "R$ ________", "R$ ________"],
    ["2", "Implantacao da plataforma SaaS multi-tenant e Central Super Admin", "1", "modulo", "R$ ________", "R$ ________"],
    ["3", "Configuracao de autenticacao, perfis, permissoes e LGPD", "1", "modulo", "R$ ________", "R$ ________"],
    ["4", "Portal publico institucional do tenant", "1", "modulo", "R$ ________", "R$ ________"],
    ["5", "Painel administrativo da escola legislativa", "1", "modulo", "R$ ________", "R$ ________"],
    ["6", "Gestao academica: cursos, turmas, aulas, alunos e matriculas", "1", "modulo", "R$ ________", "R$ ________"],
    ["7", "Painel do aluno com aulas, presenca, quizzes e certificados", "1", "modulo", "R$ ________", "R$ ________"],
    ["8", "Painel docente com turmas, aulas, presencas, avisos e quizzes", "1", "modulo", "R$ ________", "R$ ________"],
    ["9", "Eventos, inscricoes, controle de presenca e PDFs operacionais", "1", "modulo", "R$ ________", "R$ ________"],
    ["10", "Certificados digitais, templates, emissao, download e validacao publica", "1", "modulo", "R$ ________", "R$ ________"],
    ["11", "Noticias, contato do portal, e-mails e filas", "1", "modulo", "R$ ________", "R$ ________"],
    ["12", "API REST, auditoria, logs e base para relatorios/exportacoes", "1", "modulo", "R$ ________", "R$ ________"],
    ["13", "Testes, ajustes finais, homologacao e entrega tecnica", "1", "servico", "R$ ________", "R$ ________"],
    ["", "", "", "", "Subtotal", "R$ ________"],
    ["", "", "", "", "Desconto", "R$ ________"],
    ["", "", "", "", "Total geral", "R$ ________"],
]

timeline_rows = [
    ["Etapa", "Descricao", "Prazo estimado", "Responsavel"],
    ["1", "Alinhamento, dados da empresa, escopo final e priorizacao", "____ dias", "Contratada/Contratante"],
    ["2", "Configuracao/implantacao da base e identidade visual", "____ dias", "Contratada"],
    ["3", "Homologacao dos modulos administrativos e portal publico", "____ dias", "Contratada/Contratante"],
    ["4", "Homologacao aluno/docente/eventos/certificados/quizzes", "____ dias", "Contratada/Contratante"],
    ["5", "Treinamento, ajustes finais e publicacao", "____ dias", "Contratada"],
]

content = []
content.append(paragraph("PROPOSTA COMERCIAL / ORCAMENTO", "Title", bold=True))
content.append(paragraph("Sistema Legiscola - Plataforma SaaS para Escola Legislativa", bold=True))
content.append(paragraph("Documento editavel para preenchimento de dados comerciais, valores, prazos e informacoes da empresa."))
content.append(paragraph())

content.append(heading("1. Dados da Proposta"))
content.append(table([
    ["Campo", "Informacao"],
    ["Empresa contratada", "____________________________________________"],
    ["CNPJ", "____________________________________________"],
    ["Endereco", "____________________________________________"],
    ["Responsavel comercial", "____________________________________________"],
    ["E-mail / telefone", "____________________________________________"],
    ["Cliente / orgao contratante", "____________________________________________"],
    ["Data da proposta", "____/____/________"],
    ["Validade da proposta", "____ dias"],
]))

content.append(heading("2. Objetivo"))
content.append(paragraph(
    "Esta proposta tem como objetivo apresentar o escopo funcional e comercial do Sistema Legiscola, uma plataforma web SaaS para gestao de escolas legislativas, com portal publico, area administrativa, area do aluno, area docente, certificados, eventos, cursos, turmas, presencas, quizzes/provas, comunicacao e recursos de seguranca, auditoria e multi-tenancy."
))

content.append(heading("3. Tecnologias e Base Tecnica"))
for item in [
    "Backend em Laravel 13 e PHP 8.3.",
    "Frontend com Blade, Tailwind CSS, Vite, Alpine.js e Livewire.",
    "Banco de dados relacional com migrations, seeders e models Laravel.",
    "Autenticacao web e API com Laravel Sanctum.",
    "Controle de permissoes com Spatie Permission.",
    "Auditoria com Spatie ActivityLog.",
    "Geração de PDF com DomPDF e exportacoes Excel/planilhas com Maatwebsite Excel/PhpSpreadsheet.",
    "Estrutura preparada para consumo futuro por aplicativo mobile/Ionic.",
]:
    content.append(bullet(item))

content.append(heading("4. Escopo Funcional do Sistema"))
content.append(table(modules))

content.append(heading("5. Itens do Orcamento"))
content.append(paragraph("Preencha os valores conforme sua estrategia comercial, escopo contratado e condicoes negociadas."))
content.append(table(budget_rows))

content.append(heading("6. Condicoes Comerciais"))
for item in [
    "Forma de pagamento: ____________________________________________.",
    "Entrada/sinal: R$ ____________________ ou ______%.",
    "Parcelamento: ____________________________________________.",
    "Prazo de entrega estimado: ____________________________________________.",
    "Hospedagem, dominio, e-mail transacional, SMS, WhatsApp, certificados digitais ou servicos de terceiros: ( ) incluidos  ( ) nao incluidos.",
    "Suporte incluso por: ______ dias apos a entrega/homologacao.",
    "Manutencao mensal opcional: R$ ____________________.",
]:
    content.append(bullet(item))

content.append(heading("7. Cronograma Sugerido"))
content.append(table(timeline_rows))

content.append(heading("8. Entregaveis"))
for item in [
    "Sistema web configurado conforme escopo contratado.",
    "Acesso administrativo para gestao da plataforma e/ou tenant.",
    "Portal publico do tenant com configuracoes institucionais.",
    "Modulos de aluno, docente, cursos, turmas, eventos, certificados e quizzes conforme contratado.",
    "Orientacao inicial de uso para usuarios-chave.",
    "Documentacao operacional basica, quando contratada.",
]:
    content.append(bullet(item))

content.append(heading("9. Premissas e Observacoes"))
for item in [
    "Valores podem ser ajustados conforme personalizacoes, integracoes externas, importacao de dados e volume de cadastros.",
    "Conteudos institucionais, logotipos, textos, imagens, dados legais e politicas internas devem ser fornecidos pelo contratante.",
    "Integracoes com meios de pagamento, assinatura digital, WhatsApp, SMS, sistemas externos ou aplicativo mobile podem ser orcadas separadamente.",
    "Alteracoes fora do escopo aprovado podem gerar novo prazo e novo custo.",
    "Ambiente de hospedagem/producao deve atender aos requisitos tecnicos do Laravel, PHP, banco de dados, filas e armazenamento de arquivos.",
]:
    content.append(bullet(item))

content.append(heading("10. Aceite da Proposta"))
content.append(paragraph("Declaro estar de acordo com o escopo, valores, prazos e condicoes apresentados nesta proposta."))
content.append(paragraph())
content.append(table([
    ["Contratada", "Contratante"],
    ["Nome: ______________________________", "Nome: ______________________________"],
    ["Assinatura: _________________________", "Assinatura: _________________________"],
    ["Data: ____/____/________", "Data: ____/____/________"],
]))

document = doc_xml("\n".join(content))

with zipfile.ZipFile(OUTPUT, "w", compression=zipfile.ZIP_DEFLATED) as docx:
    docx.writestr(
        "[Content_Types].xml",
        """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
  <Override PartName="/word/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml"/>
</Types>
""",
    )
    docx.writestr(
        "_rels/.rels",
        """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>
""",
    )
    docx.writestr(
        "word/_rels/document.xml.rels",
        """<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"/>
""",
    )
    docx.writestr("word/document.xml", document)
    docx.writestr("word/styles.xml", styles_xml())

print(OUTPUT)
