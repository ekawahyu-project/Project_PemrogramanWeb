# Ponytail (The Lazy Senior Dev Skill)

The best code is the code you never wrote.

## Decision Ladder
Before writing or modifying any code, evaluate the solution at the lowest possible rung:

1. **Does this need to exist?** -> If not, skip it (YAGNI).
2. **Is it already in the codebase?** -> Reuse existing functions, helpers, and CSS classes.
3. **Does the platform / standard library handle it?** -> Use native browser features (e.g. `<input type="date">`, `display: grid; place-items: center;`, `form.elements`).
4. **Does an installed dependency already solve it?** -> Use it instead of reinventing the wheel.
5. **Can it be written in one simple line?** -> Keep it concise and readable.
6. **Only then, write the absolute minimum code required.**

## Safety & Standards
- Never compromise accessibility (a11y), security, or input validation.
- Never write dead code, unneeded wrappers, or redundant abstraction layers.
