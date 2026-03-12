# VETTRYX WP WhatsApp Widget

> ⚠️ **Atenção:** Este repositório atua exclusivamente como um **Submódulo** do ecossistema principal `VETTRYX WP Core`. Ele não deve ser instalado como um plugin standalone (isolado) nos clientes.

Este submódulo é uma solução nativa e ultraleve para injetar um botão flutuante do WhatsApp no front-end do WordPress. Ele elimina a dependência de plugins de terceiros pesados, focando na otimização da conversão sem penalizar a performance (PageSpeed) do site do cliente.

## 🚀 Funcionalidades

* **Performance Extrema (Zero Assets):** Não carrega arquivos de CSS ou JavaScript externos. Injeta o estilo minificado e o HTML diretamente no rodapé (`wp_footer`), utilizando um ícone SVG inline para evitar requisições de webfonts.
* **Integração Segura (API Oficial):** Constrói dinamicamente a URL utilizando a API oficial (`wa.me`), higienizando o número de telefone (removendo caracteres especiais) e codificando adequadamente a mensagem pré-definida (`urlencode`).
* **Controle de Visibilidade:** Opção nativa via CSS para ocultar o botão em dispositivos móveis ou computadores (Desktop), ideal para layouts que já possuem botões focados em resoluções específicas.
* **Personalização Simples:** Permite escolher o lado da tela (Direito/Esquerdo), o número de destino e o texto inicial da conversa de forma intuitiva.
* **White-Label:** Fica encapsulado silenciosamente dentro do menu "VETTRYX Tech" no painel do cliente.

## ⚙️ Arquitetura e Deploy (CI/CD)

Este repositório não gera mais arquivos `.zip` para instalação manual. O fluxo de deploy é 100% automatizado:

1. Qualquer push na branch `main` deste repositório dispara um webhook (Repository Dispatch) para o repositório principal do Core.
2. O repositório do Core puxa este código atualizado para dentro da pasta `/modules/`.
3. O GitHub Actions do Core empacota tudo e gera uma única Release oficial.

## 📖 Como Usar

Uma vez que o **VETTRYX WP Core** esteja instalado e o módulo WhatsApp Widget ativado no painel do cliente:

1. No menu lateral do WordPress, acesse **VETTRYX Tech > WhatsApp Widget**.
2. Preencha o "Número de Telefone" (incluindo DDI e DDD), digite a "Mensagem Pré-definida" e ajuste as preferências de "Posição no Ecrã" e "Ocultar Botão" conforme a necessidade do projeto.
3. Salve as alterações. O botão flutuante será renderizado automaticamente no front-end do site, pronto para uso.

---

**VETTRYX Tech**
*Transformando ideias em experiências digitais.*
