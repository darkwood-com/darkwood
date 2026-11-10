# SketchUp Shape

This project helps you go from **a plain-language idea** (“a simple house with a pitched roof”) to **instructions SketchUp can run** so the 3D model is created for you.

You do **not** need to know how to program SketchUp’s Ruby API yourself. The system does that part.

## What you get at the end

The main output is a **Ruby script**: a text file full of SketchUp commands. In SketchUp, you can run that script (for example via **Extensions → Ruby Console** or your usual workflow for running Ruby) and it will build the geometry described in your original idea.

Optionally, you can also save an intermediate **design specification**: a structured description of the shape (sizes, parts, hierarchy) before it is turned into code. That is useful if you want to see or archive *what* was planned before *how* it was coded.

## The journey in three steps

Everything starts with your **intention**: one sentence or short paragraph describing the shape you want.

### 1. Understanding your request

The tool checks that your description is present and usable (not empty). This step is only about making sure there is a clear starting point.

### 2. From words to a design plan

An **AI assistant** (running on a **local** model so your data stays on your machine) reads your intention and produces a **design specification**. Think of it as an architect’s brief: what parts exist, how they relate, dimensions where relevant—expressed in a structured form the next step can rely on.

So: **natural language → structured design plan.**

### 3. From design plan to SketchUp code

A second pass uses that design plan and asks the AI to write **SketchUp Ruby** that implements it. The raw answer is cleaned up so you get a proper script block you can run in SketchUp.

So: **structured design plan → Ruby script.**

## Why two stages?

Splitting “design” and “code” keeps things clearer:

- The **design** step focuses on *what* to build and *how parts fit together*.
- The **code** step focuses on *SketchUp’s way* of creating faces, groups, and operations.

That separation usually gives more reliable results than asking for code directly from a vague sentence alone.

## What runs where

Inference uses **Ollama** (or compatible setup) on your computer, not a cloud API by default—so your shape descriptions are not sent to an external AI service unless you change the configuration.

## Command-line examples

From the project directory, after dependencies are installed ([PHP](https://www.php.net/) 8.4+, [Composer](https://getcomposer.org/), and `composer install`).

**1. Download the default Ollama model** (once, or when you change models):

```bash
ollama pull ministral-3:8b
```

**2. Start the Ollama server** (so the app can talk to the model):

```bash
ollama serve
```

On macOS or Windows, the Ollama app often runs this for you in the background; use `ollama serve` only if nothing is listening on the Ollama port.

**3. Generate a SketchUp Ruby script** from a short description (from the project root):

```bash
bin/console app:generate-shape "Create a house"
```

If that is not found, use `php bin/console` or `./bin/console` instead.

The Ruby script is printed to the terminal. To write it to a file, use `-o` / `--output`:

```bash
bin/console app:generate-shape "Create a house" -o house.rb
```

Optional: save the JSON design spec with `--spec path/to/spec.json`.

Configuration (Ollama URL, model name, timeouts) uses environment variables such as `OLLAMA_HOST_URL` and `OLLAMA_MODEL`; see `.env` / `.env.local`.

## Summary

| You provide | The system produces |
|-------------|---------------------|
| A description of the shape you want | A **design spec** (optional file) |
| | A **Ruby script** you run inside SketchUp to build the model |

If you only care about the result in SketchUp, the important line is: **describe the shape → get Ruby → run it in SketchUp.**
