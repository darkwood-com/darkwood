NAVI_DIR := src/Darkwood/Component/Navi
NAVI_MAKE := $(MAKE) -C $(NAVI_DIR)

.DEFAULT_GOAL := help

# Forward all targets to src/Darkwood/Component/Navi/Makefile
%:
	@$(NAVI_MAKE) $(MAKECMDGOALS) $(if $(ARGS),ARGS="$(ARGS)",)

help:
	@$(NAVI_MAKE) help

.PHONY: help
